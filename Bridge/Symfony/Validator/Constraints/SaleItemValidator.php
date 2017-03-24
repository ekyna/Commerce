<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class SaleItemValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleItemValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($item, SaleItemInterface::class);
        }
        if (!$constraint instanceof SaleItem) {
            throw new UnexpectedTypeException($constraint, SaleItem::class);
        }

        // Tax group must not be null if item has no children
        if (!$item->hasChildren() && null === $item->getTaxGroup()) {
            $this
                ->context
                ->buildViolation($constraint->tax_group_must_not_be_null)
                ->atPath('taxGroup')
                ->addViolation();
        }

        $this->checkShipmentIntegrity($item, $constraint);
    }

    /**
     * Checks that the sale item quantity is lower than or equals the shipped quantity.
     *
     * @param SaleItemInterface $item
     * @param SaleItem $constraint
     */
    protected function checkShipmentIntegrity(SaleItemInterface $item, SaleItem $constraint)
    {
        $sale = $item->getSale();
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return;
        }

        $quantity = 0;

        $shipments = $sale->getShipments();
        /** @var Shipment\ShipmentInterface $shipment */
        foreach ($shipments as $shipment) {
            if (!Shipment\ShipmentStates::isStockableState($shipment->getState())) {
                continue;
            }

            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $item) {
                    $quantity += $shipmentItem->getQuantity();
                }
            }
        }

        if (0 < $quantity && $item->getTotalQuantity() < $quantity) {
            $this
                ->context
                ->buildViolation($constraint->shipment_integrity)
                ->atPath('quantity')
                ->addViolation();
        }
    }
}
