<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class OrderValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($order, Constraint $constraint)
    {
        if (!$order instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of " . OrderInterface::class);
        }
        if (!$constraint instanceof Order) {
            throw new InvalidArgumentException("Expected instance of " . Order::class);
        }

        if ($order->isSample() && ($order->hasPayments() || $order->hasInvoices())) {
            $this
                ->context
                ->buildViolation($constraint->sample_with_payments_or_invoices)
                ->atPath('sample')
                ->addViolation();
        }

        if (null !== $originCustomer = $order->getOriginCustomer()) {
            if (!$originCustomer->hasParent()) {
                $this
                    ->context
                    ->buildViolation($constraint->unexpected_origin_customer)
                    ->atPath('originCustomer')
                    ->addViolation();

                return;
            }

            if ($originCustomer->getParent() !== $order->getCustomer()) {
                $this
                    ->context
                    ->buildViolation($constraint->customers_integrity)
                    ->atPath('originCustomer')
                    ->addViolation();

                return;
            }
        }
    }
}
