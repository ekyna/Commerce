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

        // Requirements
        if ($accounting->getType() === AccountingTypes::TYPE_PAYMENT) {
            $paymentMethod = true;
            $tax = false;
        } elseif ($accounting->getType() === AccountingTypes::TYPE_TAX) {
            $tax = true;
        } else {
            $taxRule = true;
        }

        // Tax assertion
        if ($tax && !$accounting->getTax()) {
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
        if ($taxRule && !$accounting->getTaxRule()) {
            $this
                ->context
                ->buildViolation($constraint->tax_rule_is_required)
                ->atPath('taxRule')
                ->addViolation();
        } elseif (!$taxRule && $accounting->getTaxRule()) {
            $this
                ->context
                ->buildViolation($constraint->tax_rule_must_be_null)
                ->atPath('taxRule')
                ->addViolation();
        }

        // Payment method assertion
        if ($paymentMethod && !$accounting->getPaymentMethod()) {
            $this
                ->context
                ->buildViolation($constraint->payment_method_is_required)
                ->atPath('paymentMethod')
                ->addViolation();
        } elseif (!$paymentMethod && $accounting->getPaymentMethod()) {
            $this
                ->context
                ->buildViolation($constraint->payment_method_must_be_null)
                ->atPath('paymentMethod')
                ->addViolation();
        }
    }
}
