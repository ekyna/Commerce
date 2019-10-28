<?php

namespace Ekyna\Component\Commerce\Support\EventListener;

use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Support\Event\TicketEvents;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class TicketMessageEventListener
 * @package Ekyna\Component\Commerce\Support\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageEventListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        $this->scheduleTicketContentChangeEvent($message->getTicket());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        $this->scheduleTicketContentChangeEvent($message->getTicket());
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        $message = $this->getMessageFromEvent($event);

        if (null === $ticket = $message->getTicket()) {
            $ticket = $this->persistenceHelper->getChangeSet($message, 'ticket')[0];
        }

        $this->scheduleTicketContentChangeEvent($ticket);
    }

    /**
     * @inheritdoc
     */
    protected function scheduleTicketContentChangeEvent(TicketInterface $ticket): void
    {
        $this->persistenceHelper->scheduleEvent(TicketEvents::CONTENT_CHANGE, $ticket);
    }

    /**
     * Returns the message from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return TicketMessageInterface
     */
    protected function getMessageFromEvent(ResourceEventInterface $event): TicketMessageInterface
    {
        $message = $event->getResource();

        if (!$message instanceof TicketMessageInterface) {
            throw new UnexpectedValueException("Expected instance of " . TicketMessageInterface::class);
        }

        return $message;
    }
}
