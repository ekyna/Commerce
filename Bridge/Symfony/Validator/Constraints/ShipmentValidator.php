<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ShipmentValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($shipment, Constraint $constraint)
    {
        if (null === $shipment) {
            return;
        }

        if (!$shipment instanceof ShipmentInterface) {
            throw new UnexpectedTypeException($shipment, ShipmentInterface::class);
        }
        if (!$constraint instanceof Shipment) {
            throw new UnexpectedTypeException($constraint, Shipment::class);
        }

        /**
         * Shipment can't have a stockable state if order is not in a stockable state
         */
        if (ShipmentStates::isStockableState($shipment->getState())) {
            $sale = $shipment->getSale();

            // Only orders are supported.
            if (!$sale instanceof OrderInterface) {
                throw new UnexpectedTypeException($sale, OrderInterface::class);
            }
            if (!OrderStates::isStockableState($sale->getState())) {
                $this
                    ->context
                    ->buildViolation($constraint->shipped_state_denied)
                    ->setInvalidValue($shipment->getState())
                    ->atPath('state')
                    ->addViolation();
            }
        }
    }
}
