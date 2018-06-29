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

        if ($accounting->getType() === AccountingTypes::TYPE_TAX) {
            if (!$accounting->getTax()) {
                $this
                    ->context
                    ->buildViolation($constraint->tax_is_required)
                    ->atPath('tax')
                    ->addViolation();
            }
            if ($accounting->getTaxRule()) {
                $this
                    ->context
                    ->buildViolation($constraint->rule_must_be_null)
                    ->atPath('taxRule')
                    ->addViolation();
            }
        } else {
            if ($accounting->getTax()) {
                $this
                    ->context
                    ->buildViolation($constraint->tax_must_be_null)
                    ->atPath('tax')
                    ->addViolation();
            }
            if (!$accounting->getTaxRule()) {
                $this
                    ->context
                    ->buildViolation($constraint->rule_is_required)
                    ->atPath('taxRule')
                    ->addViolation();
            }
        }
    }
}
