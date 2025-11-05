<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class AdjustmentValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof AdjustmentInterface) {
            throw new UnexpectedTypeException($value, AdjustmentInterface::class);
        }
        if (!$constraint instanceof Adjustment) {
            throw new UnexpectedTypeException($constraint, Adjustment::class);
        }

        if ($value->getType() !== AdjustmentTypes::TYPE_DISCOUNT) {
            $violationList = $this
                ->context
                ->getValidator()
                ->validate($value->getDesignation(), [new Assert\NotBlank()]);

            /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
            foreach ($violationList as $violation) {
                $this->context
                    ->buildViolation($violation->getMessage())
                    ->atPath('designation')
                    ->addViolation();
            }
        }
    }
}
