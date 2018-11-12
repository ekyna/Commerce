<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Accounting\Model\AccountingInterface;
use Ekyna\Component\Commerce\Accounting\Model\AccountingTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class AccountingValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AccountingValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($accounting, Constraint $constraint)
    {
        if (!$accounting instanceof AccountingInterface) {
            throw new UnexpectedTypeException($accounting, AccountingInterface::class);
        }
        if (!$constraint instanceof Accounting) {
            throw new UnexpectedTypeException($constraint, Accounting::class);
        }

        $tax = null;
        $taxRule = false;
        $paymentMethod = false;
        $customerGroups = false;

        // Requirements
        if ($accounting->getType() === AccountingTypes::TYPE_PAYMENT) {
            $paymentMethod = true;
            $tax = false;
        } elseif ($accounting->getType() === AccountingTypes::TYPE_UNPAID) {
            $customerGroups = null;
        } elseif ($accounting->getType() === AccountingTypes::TYPE_TAX) {
            $tax = true;
        } else {
            $taxRule = true;
        }

        // Tax assertion
        if (!$accounting->getTax() && $tax) {
            $this
                ->context
                ->buildViolation($constraint->tax_is_required)
                ->atPath('tax')
                ->addViolation();
        } elseif ($accounting->getTax() && false === $tax) {
            $this
                ->context
                ->buildViolation($constraint->tax_must_be_null)
                ->atPath('tax')
                ->addViolation();
        }

        // Tax rule assertion
        if (!$accounting->getTaxRule() && $taxRule) {
            $this
                ->context
                ->buildViolation($constraint->tax_rule_is_required)
                ->atPath('taxRule')
                ->addViolation();
        } elseif ($accounting->getTaxRule() && false === $taxRule) {
            $this
                ->context
                ->buildViolation($constraint->tax_rule_must_be_null)
                ->atPath('taxRule')
                ->addViolation();
        }

        // Payment method assertion
        if (!$accounting->getPaymentMethod() && $paymentMethod) {
            $this
                ->context
                ->buildViolation($constraint->payment_method_is_required)
                ->atPath('paymentMethod')
                ->addViolation();
        } elseif ($accounting->getPaymentMethod() && false === $paymentMethod) {
            $this
                ->context
                ->buildViolation($constraint->payment_method_must_be_null)
                ->atPath('paymentMethod')
                ->addViolation();
        }

        // Customer groups assertion
        if ((0 === $accounting->getCustomerGroups()->count()) && $customerGroups) {
            $this
                ->context
                ->buildViolation($constraint->customer_groups_is_required)
                ->atPath('customerGroups')
                ->addViolation();
        } elseif ((0 < $accounting->getCustomerGroups()->count()) && false === $customerGroups) {
            $this
                ->context
                ->buildViolation($constraint->customer_groups_must_be_empty)
                ->atPath('customerGroups')
                ->addViolation();
        }
    }
}
