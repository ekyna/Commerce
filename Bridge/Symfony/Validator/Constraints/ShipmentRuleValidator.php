<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentRuleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;

/**
 * Class ShipmentRuleValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRuleValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($rule, Constraint $constraint)
    {
        if (!$rule instanceof ShipmentRuleInterface) {
            throw new InvalidArgumentException("Expected instance of " . ShipmentRuleInterface::class);
        }
        if (!$constraint instanceof ShipmentRule) {
            throw new InvalidArgumentException("Expected instance of " . ShipmentRule::class);
        }

        if (null === $startAt = $rule->getStartAt()) {
            return;
        }

        if (null === $endAt = $rule->getEndAt()) {
            return;
        }

        if ($startAt > $endAt) {
            $this
                ->context
                ->buildViolation($constraint->start_at_greater_than_end_at)
                ->atPath('endAt')
                ->setInvalidValue($endAt)
                ->addViolation();
        }
    }
}
