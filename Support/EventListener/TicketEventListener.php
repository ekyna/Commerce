<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\EventListener;

use DateTime;
use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class TicketEventListener
 * @package Ekyna\Component\Commerce\Support\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketEventListener
{
    protected PersistenceHelperInterface $persistenceHelper;
    protected GeneratorInterface         $numberGenerator;
    protected StateResolverInterface     $stateResolver;

    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        GeneratorInterface         $numberGenerator,
        StateResolverInterface     $stateResolver
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->numberGenerator = $numberGenerator;
        $this->stateResolver = $stateResolver;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $ticket = $this->getTicketFromEvent($event);

        if ($this->handleInsert($ticket)) {
            $this->persistenceHelper->persistAndRecompute($ticket, false);
        }
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $ticket = $this->getTicketFromEvent($event);

        if ($this->handleUpdate($ticket)) {
            $this->persistenceHelper->persistAndRecompute($ticket, false);
        }
    }

    public function onContentChange(ResourceEventInterface $event): void
    {
        $ticket = $this->getTicketFromEvent($event);

        if ($this->updateState($ticket)) {
            $this->persistenceHelper->persistAndRecompute($ticket, false);
        }
    }

    protected function handleInsert(TicketInterface $ticket): bool
    {
        $changed = $this->updateNumber($ticket);

        $changed = $this->updateState($ticket) || $changed;

        return $this->fixCustomer($ticket) || $changed;
    }

    protected function handleUpdate(TicketInterface $ticket): bool
    {
        $changed = $this->updateNumber($ticket);

        $changed = $this->updateState($ticket) || $changed;

        return $this->fixCustomer($ticket) || $changed;
    }

    protected function handleContentChange(TicketInterface $ticket): bool
    {
        return $this->updateState($ticket);
    }

    protected function updateState(TicketInterface $ticket): bool
    {
        $changed = $this->stateResolver->resolve($ticket);

        $closed = $ticket->getState() === TicketStates::STATE_CLOSED;
        $set = null !== $ticket->getClosedAt();

        if ($closed && !$set) {
            $ticket->setClosedAt(new DateTime());
        } elseif (!$closed && $set) {
            $ticket->setClosedAt(null);
        }

        return $changed;
    }

    protected function updateNumber(TicketInterface $ticket): bool
    {
        if (!empty($ticket->getNumber())) {
            return false;
        }

        $ticket->setNumber($this->numberGenerator->generate($ticket));

        return true;
    }

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

    protected function getTicketFromEvent(ResourceEventInterface $event): TicketInterface
    {
        $ticket = $event->getResource();

        if (!$ticket instanceof TicketInterface) {
            throw new UnexpectedTypeException($ticket, TicketInterface::class);
        }

        return $ticket;
    }
}
