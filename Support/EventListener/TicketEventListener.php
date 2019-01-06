<?php

namespace Ekyna\Component\Commerce\Support\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
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
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param NumberGeneratorInterface   $numberGenerator
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        NumberGeneratorInterface $numberGenerator
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

        if ($this->handleUpdate($ticket)) {
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
        return $this->updateNumber($ticket);
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
        return $this->updateNumber($ticket);
    }

    /**
     * Updates the number.
     *
     * @param TicketInterface $ticket
     *
     * @return bool Whether the sale number has been update.
     */
    protected function updateNumber(TicketInterface $ticket)
    {
        if (0 == strlen($ticket->getNumber())) {
            $this->numberGenerator->generate($ticket);

            return true;
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
