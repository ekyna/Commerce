<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Credit\Model\CreditItemInterface;
use Ekyna\Component\Commerce\Credit\Util\CreditUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class CreditItemValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CreditItemValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof CreditItemInterface) {
            throw new UnexpectedTypeException($item, CreditItemInterface::class);
        }
        if (!$constraint instanceof CreditItem) {
            throw new UnexpectedTypeException($constraint, CreditItem::class);
        }

        // ShipmentItem vs SaleItem integrity
        if (null !== $shipmentItem = $item->getShipmentItem()) {
            // Shipment must be a return
            if (!$shipmentItem->getShipment()->isReturn()) {
                $this
                    ->context
                    ->buildViolation($constraint->shipment_is_not_return)
                    ->setInvalidValue($shipmentItem)
                    ->atPath('shipmentItem')
                    ->addViolation();

                return;
            }

            // Credit's SaleItem and ShipmentItem's SaleItem must match
            if ($item->getSaleItem() !== $shipmentItem->getSaleItem()) {
                $this
                    ->context
                    ->buildViolation($constraint->sale_item_and_shipment_item_miss_match)
                    ->setInvalidValue($shipmentItem)
                    ->atPath('shipmentItem')
                    ->addViolation();

                return;
            }

            // CreditItem's quantity can't be greater than the related ShipmentItem's quantity
            if ($item->getQuantity() > $shipmentItem->getQuantity()) {
                $this
                    ->context
                    ->buildViolation($constraint->quantity_is_greater_than_returned, [
                        '%max%' => $shipmentItem->getQuantity(),
                    ])
                    ->setInvalidValue($shipmentItem)
                    ->atPath('quantity')
                    ->addViolation();

                return;
            }
        }

        // The Sale of the CreditItem's SaleItem must match the Sale of the SaleItem's Credit
        if ($item->getSaleItem()->getSale() !== $item->getCredit()->getSale()) {
            $this
                ->context
                ->buildViolation($constraint->sale_and_credit_miss_match)
                ->setInvalidValue($item->getSaleItem())
                ->atPath('saleItem')
                ->addViolation();

            return;
        }

        // CreditItem's quantity can't be greater than the creditable quantity
        $available = CreditUtil::calculateCreditableQuantity($item->getSaleItem());
        if ($item->getQuantity() > $available) {
            $this
                ->context
                ->buildViolation($constraint->quantity_is_greater_than_creditable, [
                    '%max%' => $available,
                ])
                ->setInvalidValue($shipmentItem)
                ->atPath('quantity')
                ->addViolation();

            return;
        }
    }
}
