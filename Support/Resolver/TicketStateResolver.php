<?php

namespace Ekyna\Component\Commerce\Support\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\AbstractStateResolver;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Ekyna\Component\Commerce\Support\Model\TicketMessageInterface;
use Ekyna\Component\Commerce\Support\Model\TicketStates;

/**
 * Class TicketStateResolver
 * @package Ekyna\Component\Commerce\Support\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketStateResolver extends AbstractStateResolver
{
    /**
     * @inheritDoc
     *
     * @param TicketInterface $ticket
     */
    protected function resolveState(object $ticket): string
    {
        if ($ticket->getState() === TicketStates::STATE_CLOSED) {
            return TicketStates::STATE_CLOSED;
        }

        if ($ticket->isInternal()) {
            return TicketStates::STATE_INTERNAL;
        }

        if (!$message = $this->findLatestNonInternalMessage($ticket)) {
            return TicketStates::STATE_NEW;
        }

        if ($message->isCustomer()) {
            return TicketStates::STATE_OPENED;
        }

        return TicketStates::STATE_PENDING;
    }

    /**
     * Returns the latest non internal message.
     *
     * @param TicketInterface $ticket
     *
     * @return TicketMessageInterface|null
     */
    private function findLatestNonInternalMessage(TicketInterface $ticket): ?TicketMessageInterface
    {
        if (0 === $ticket->getMessages()->count()) {
            return null;
        }

        /** @var TicketMessageInterface[] $messages */
        $messages = array_reverse($ticket->getMessages()->toArray());

        foreach ($messages as $message) {
            if (!$message->isInternal()) {
                return $message;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof TicketInterface) {
            throw new UnexpectedTypeException($subject, TicketInterface::class);
        }
    }
}
