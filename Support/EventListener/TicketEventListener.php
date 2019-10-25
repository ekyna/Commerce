<?php

namespace Ekyna\Component\Commerce\Support\EventListener;

use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedValueException;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class TicketEventListener
 * @package Ekyna\Component\Commerce\Support\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketEventListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var GeneratorInterface
     */
    protected $numberGenerator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param GeneratorInterface         $numberGenerator
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        GeneratorInterface $numberGenerator
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->numberGenerator = $numberGenerator;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $ticket = $this->getTicketFromEvent($event);

        if ($this->handleInsert($ticket)) {
            $this->persistenceHelper->persistAndRecompute($ticket, false);
        }
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $ticket = $this->getTicketFromEvent($event);

        if ($this->handleUpdate($ticket)) {
            $this->persistenceHelper->persistAndRecompute($ticket, false);
        }
    }

    /**
     * Handles the ticket insertion.
     *
     * @param TicketInterface $ticket
     *
     * @return bool
     */
    protected function handleInsert(TicketInterface $ticket)
    {
        $changed = $this->updateNumber($ticket);

        $changed |= $this->fixCustomer($ticket);

        return $changed;
    }

    /**
     * Handles the ticket update.
     *
     * @param TicketInterface $ticket
     *
     * @return bool
     */
    protected function handleUpdate(TicketInterface $ticket)
    {
        $changed = $this->updateNumber($ticket);

        $changed |= $this->fixCustomer($ticket);

        return $changed;
    }

    /**
     * Updates the number.
     *
     * @param TicketInterface $ticket
     *
     * @return bool Whether the sale number has been changed.
     */
    protected function updateNumber(TicketInterface $ticket): bool
    {
        if (!empty($ticket->getNumber())) {
            return false;
        }

        $ticket->setNumber($this->numberGenerator->generate($ticket));

        return true;
    }

    /**
     * Updates the number.
     *
     * @param TicketInterface $ticket
     *
     * @return bool Whether the ticket customer has been changed.
     */
    protected function fixCustomer(TicketInterface $ticket): bool
    {
        if ($ticket->getCustomer()) {
            return false;
        }

        foreach ($ticket->getOrders() as $order) {
            if ($c = $order->getCustomer()) {
                $ticket->setCustomer($c);

                return true;
            }
        }

        foreach ($ticket->getQuotes() as $quote) {
            if ($c = $quote->getCustomer()) {
                $ticket->setCustomer($c);

                return true;
            }
        }

        return false;
    }

    /**
     * Returns the ticket from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return TicketInterface
     */
    protected function getTicketFromEvent(ResourceEventInterface $event)
    {
        $ticket = $event->getResource();

        if (!$ticket instanceof TicketInterface) {
            throw new UnexpectedValueException("Expected instance of " . TicketInterface::class);
        }

        return $ticket;
    }
}
