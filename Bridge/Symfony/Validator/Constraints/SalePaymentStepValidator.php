<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SalePaymentStepValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SalePaymentStepValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint): void
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof SaleInterface) {
            throw new UnexpectedTypeException($value, SaleInterface::class);
        }
        if (!$constraint instanceof SalePaymentStep) {
            throw new UnexpectedTypeException($constraint, SalePaymentStep::class);
        }

        if (null !== $value->getShipmentMethod()) {
            return;
        }

        $this->context
            ->buildViolation($constraint->shipment_method_must_be_set)
            ->atPath('shipmentMethod')
            ->addViolation();
    }
}
