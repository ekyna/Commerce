<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\EventListener;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Support\Event\TicketEvents;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Event\ResourceMessage;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class TicketMessageEventListener
 * @package Ekyna\Component\Commerce\Support\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageEventListener
{
    protected PersistenceHelperInterface $persistenceHelper;

    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        $this->scheduleTicketContentChangeEvent($message->getTicket());
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        $this->scheduleTicketContentChangeEvent($message->getTicket());
    }

    public function onPreDelete(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        $limit = 1;
        if (null === $ticket = $message->getTicket()) {
            $ticket = $this->persistenceHelper->getChangeSet($message, 'ticket')[0];
            $limit = 0;
        }

        if ($limit < $ticket->getMessages()->count()) {
            return;
        }

        if ($this->persistenceHelper->isScheduledForRemove($ticket)) {
            return;
        }

        $event->addMessage(
            ResourceMessage::create('A ticket must have at least one message', ResourceMessage::TYPE_ERROR)
        );
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        if (null === $ticket = $message->getTicket()) {
            $ticket = $this->persistenceHelper->getChangeSet($message, 'ticket')[0];
        }

        $this->scheduleTicketContentChangeEvent($ticket);
    }

    protected function scheduleTicketContentChangeEvent(TicketInterface $ticket): void
    {
        $this->persistenceHelper->scheduleEvent($ticket, TicketEvents::CONTENT_CHANGE);
    }

    protected function getMessageFromEvent(ResourceEventInterface $event): TicketMessageInterface
    {
        $message = $event->getResource();

        if (!$message instanceof TicketMessageInterface) {
            throw new UnexpectedTypeException($message, TicketMessageInterface::class);
        }

        return $message;
    }
}
