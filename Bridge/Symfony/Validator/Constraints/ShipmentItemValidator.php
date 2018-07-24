<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class ShipmentItemValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentItemValidator extends ConstraintValidator
{
    /**
     * @var ShipmentCalculatorInterface
     */
    private $shipmentCalculator;


    /**
     * Constructor.
     *
     * @param ShipmentCalculatorInterface $calculator
     */
    public function __construct(ShipmentCalculatorInterface $calculator)
    {
        $this->shipmentCalculator = $calculator;
    }

    /**
     * @inheritDoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof ShipmentItemInterface) {
            throw new UnexpectedTypeException($item, ShipmentItemInterface::class);
        }
        if (!$constraint instanceof ShipmentItem) {
            throw new UnexpectedTypeException($constraint, ShipmentItem::class);
        }

        // Check parent/quantity integrity
        $saleItem = $item->getSaleItem();
        $shipment = $item->getShipment();

        if ($saleItem->isPrivate()) {
            // TODO Rework / Use packaging format
            $iQty = round(100000 * (float)$item->getQuantity());
            $siQty = round(100000 * (float)$saleItem->getQuantity());
            if (0 !== $mod = $iQty % $siQty) {
                $this
                    ->context
                    ->buildViolation($constraint->parent_quantity_integrity, [
                        '%multiple%' => $saleItem->getQuantity()
                    ])
                    ->setInvalidValue($item->getQuantity())
                    ->atPath('quantity')
                    ->addViolation();

                return;
            }
        }

        // Return shipment case
        if ($shipment->isReturn()) {
            $max = $this->shipmentCalculator->calculateReturnableQuantity($saleItem, $shipment);

            if ($max < $item->getQuantity()) {
                $this
                    ->context
                    ->buildViolation($constraint->returnable_overflow, [
                        '%max%' => $max
                    ])
                    ->setInvalidValue($item->getQuantity())
                    ->atPath('quantity')
                    ->addViolation();

                return;
            }

            return;
        }

        // Regular shipment case
        $max = $this->shipmentCalculator->calculateShippableQuantity($saleItem, $shipment);
        if (ShipmentStates::isStockableState($shipment->getState())) {
            $max = min($max, $this->shipmentCalculator->calculateAvailableQuantity($saleItem, $shipment));
        }
        if ($max < $item->getQuantity()) {
            $this
                ->context
                ->buildViolation($constraint->shippable_overflow, [
                    '%max%' => $max
                ])
                ->setInvalidValue($item->getQuantity())
                ->atPath('quantity')
                ->addViolation();

            return;
        }
    }
}
