<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Support\Model\TicketInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TicketValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($ticket, Constraint $constraint)
    {
        if (null === $ticket) {
            return;
        }

        if (!$ticket instanceof TicketInterface) {
            throw new UnexpectedTypeException($ticket, TicketInterface::class);
        }
        if (!$constraint instanceof Ticket) {
            throw new UnexpectedTypeException($constraint, Ticket::class);
        }

        if (is_null($customer = $ticket->getCustomer()) && $ticket->getOrders()->isEmpty() && $ticket->getQuotes()->isEmpty()) {
            $this
                ->context
                ->buildViolation($constraint->orphan)
                ->addViolation();
        }

        if (!$customer = $this->resolveCustomer($ticket)) {
            return;
        }

        foreach ($ticket->getOrders() as $order) {
            if (!$this->checkCustomer($customer, $order)) {
                $this
                    ->context
                    ->buildViolation($constraint->customers_integrity)
                    ->atPath('orders')
                    ->addViolation();
            }
        }

        foreach ($ticket->getQuotes() as $quote) {
            if (!$this->checkCustomer($customer, $quote)) {
                $this
                    ->context
                    ->buildViolation($constraint->customers_integrity)
                    ->atPath('quotes')
                    ->addViolation();
            }
        }
    }

    /**
     * Checks whether ticket customer and sale customer matches.
     *
     * @param CustomerInterface $customer
     * @param SaleInterface     $sale
     *
     * @return bool
     */
    private function checkCustomer(CustomerInterface $customer, SaleInterface $sale): bool
    {
        if (!$c = $sale->getCustomer()) {
            return false;
        }
        
        if ($customer === $c) {
            return true;
        }
        
        if ($customer === $c->getParent()) {
            return true;
        }
        
        if ($customer->getParent() === $c) {
            return true;
        }

        if (!$sale instanceof OrderInterface) {
            return false;
        }

        if (!$c = $sale->getOriginCustomer()) {
            return false;
        }
        
        if ($customer === $c) {
            return true;
        }
        
        if ($customer === $c->getParent()) {
            return true;
        }
        
        if ($customer->getParent() === $c) {
            return true;
        }

        return false;
    }

    /**
     * Resolves the customer from ticket/order/quotes.
     *
     * @param TicketInterface $ticket
     *
     * @return CustomerInterface|null
     */
    private function resolveCustomer(TicketInterface $ticket): ?CustomerInterface
    {
        if ($customer = $ticket->getCustomer()) {
            return $customer;
        }

        foreach ($ticket->getOrders() as $order) {
            if ($customer = $order->getCustomer()) {
                return $customer;
            }
        }

        foreach ($ticket->getQuotes() as $quote) {
            if ($customer = $quote->getCustomer()) {
                return $customer;
            }
        }

        return null;
    }
}
