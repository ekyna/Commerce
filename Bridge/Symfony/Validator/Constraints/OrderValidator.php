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

        $this->validateOriginCustomer($value, $constraint);

        $this->validateCompanyNumber($value, $constraint);
    }

    private function validateOriginCustomer(OrderInterface $order, Order $constraint): void
    {
        if (null === $originCustomer = $order->getOriginCustomer()) {
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

        if ($originCustomer->getParent() === $order->getCustomer()) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->customers_integrity)
            ->atPath('originCustomer')
            ->addViolation();
    }

    private function validateCompanyNumber(OrderInterface $order, Order $constraint): void
    {
        if (null === $group = $order->getCustomerGroup()) {
            return;
        }

        if (!$group->isBusiness()) {
            return;
        }

        if (!empty($order->getCompanyNumber())) {
            return;
        }

        $this
            ->context
            ->buildViolation($constraint->required_company_number)
            ->atPath('companyNumber')
            ->addViolation();
    }
}
