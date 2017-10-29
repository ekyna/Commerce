<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber;
use Symfony\Component\PropertyAccess\PropertyAccess;
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
     * {@inheritdoc}
     */
    public function validate($adjustment, Constraint $constraint)
    {
        if (null === $adjustment) {
            return;
        }

        if (!$adjustment instanceof AdjustmentInterface) {
            throw new UnexpectedTypeException($adjustment, AdjustmentInterface::class);
        }
        if (!$constraint instanceof Adjustment) {
            throw new UnexpectedTypeException($constraint, Adjustment::class);
        }

        if ($adjustment->getType() !== AdjustmentTypes::TYPE_DISCOUNT) {
            $violationList = $this
                ->context
                ->getValidator()
                ->validate($adjustment->getDesignation(), [new Assert\NotBlank()]);

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
