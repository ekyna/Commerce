<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class TaxRuleValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleValidator extends ConstraintValidator
{
    /**
     * @inheritdoc
     */
    public function validate($taxRule, Constraint $constraint)
    {
        if (null === $taxRule) {
            return;
        }

        if (!$taxRule instanceof TaxRuleInterface) {
            throw new UnexpectedTypeException($taxRule, TaxRuleInterface::class);
        }
        if (!$constraint instanceof TaxRule) {
            throw new UnexpectedTypeException($constraint, TaxRule::class);
        }

        if (!$taxRule->isCustomer() && !$taxRule->isBusiness()) {
            $this->context
                ->buildViolation($constraint->at_least_customer_or_business)
                ->addViolation();
        }
    }
}
