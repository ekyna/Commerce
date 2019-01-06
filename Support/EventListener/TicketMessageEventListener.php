<?php

namespace Ekyna\Component\Commerce\Support\EventListener;

use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
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
    public function onInsert(ResourceEventInterface $event)
    {
        $message = $this->getMessageFromEvent($event);

        $this->updateTicket($message);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $message = $this->getMessageFromEvent($event);

        $this->updateTicket($message);
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        // TODO Update ticket regarding to last message (not this one)
    }

    /**
     * Updates the ticket.
     *
     * @param TicketMessageInterface $message
     */
    protected function updateTicket(TicketMessageInterface $message)
    {
        $ticket = $message->getTicket()->setUpdatedAt(new \DateTime());

        if ($message->isLatest() && ($ticket->getState() !== TicketStates::STATE_CLOSED)) {
            if ($message->isCustomer()) {
                if ($ticket->getState() === TicketStates::STATE_PENDING) {
                    $ticket->setState(TicketStates::STATE_OPENED);
                }
            } elseif ($ticket->getState() === TicketStates::STATE_OPENED) {
                $ticket->setState(TicketStates::STATE_PENDING);
            }
        }

        $this->persistenceHelper->persistAndRecompute($ticket, false);
    }

    /**
     * Returns the message from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return TicketMessageInterface
     */
    protected function getMessageFromEvent(ResourceEventInterface $event)
    {
        $message = $event->getResource();

        if (!$message instanceof TicketMessageInterface) {
            throw new UnexpectedValueException("Expected instance of " . TicketMessageInterface::class);
        }

        return $message;
    }
}
