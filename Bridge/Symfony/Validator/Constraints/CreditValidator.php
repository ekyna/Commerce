<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Credit\Model\CreditInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class CreditValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreditValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($credit, Constraint $constraint)
    {
        if (null === $credit) {
            return;
        }

        if (!$credit instanceof CreditInterface) {
            throw new UnexpectedTypeException($credit, CreditInterface::class);
        }
        if (!$constraint instanceof Credit) {
            throw new UnexpectedTypeException($constraint, Credit::class);
        }

        // Shipment / Sale integrity
        if (null !== $shipment = $credit->getShipment()) {
            if ($credit->getSale() !== $shipment->getSale()) {
                $this
                    ->context
                    ->buildViolation($constraint->hierarchy_integrity)
                    ->setInvalidValue($shipment)
                    ->atPath('shipment')
                    ->addViolation();
            }
        }
    }
}
