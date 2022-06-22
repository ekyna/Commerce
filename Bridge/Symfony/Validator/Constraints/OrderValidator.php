<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

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
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof OrderInterface) {
            throw new UnexpectedTypeException($value, OrderInterface::class);
        }
        if (!$constraint instanceof Order) {
            throw new UnexpectedTypeException($constraint, Order::class);
        }

        if ($value->isSample() && ($value->hasPayments() || $value->hasInvoices())) {
            $this
                ->context
                ->buildViolation($constraint->sample_with_payments_or_invoices)
                ->atPath('sample')
                ->addViolation();
        }

        if (null === $originCustomer = $value->getOriginCustomer()) {
            return;
        }

        if (!$originCustomer->hasParent()) {
            $this
                ->context
                ->buildViolation($constraint->unexpected_origin_customer)
                ->atPath('originCustomer')
                ->addViolation();

            return;
        }

        if ($originCustomer->getParent() !== $value->getCustomer()) {
            $this
                ->context
                ->buildViolation($constraint->customers_integrity)
                ->atPath('originCustomer')
                ->addViolation();
        }
    }
}
