<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Invoice\Util\InvoiceUtil;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Class InvoiceLineValidator
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceLineValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate($item, Constraint $constraint)
    {
        if (null === $item) {
            return;
        }

        if (!$item instanceof InvoiceLineInterface) {
            throw new UnexpectedTypeException($item, InvoiceLineInterface::class);
        }
        if (!$constraint instanceof InvoiceLine) {
            throw new UnexpectedTypeException($constraint, InvoiceLine::class);
        }

        if ($item->getType() === DocumentLineTypes::TYPE_GOOD) {
            if (null === $item->getSaleItem()) {
                $this
                    ->context
                    ->buildViolation($constraint->shipment_is_not_return)
                    ->setInvalidValue(null)
                    ->atPath('saleItem')
                    ->addViolation();

                return;
            }
        }

        return; // TODO

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

            // Invoice's SaleItem and ShipmentItem's SaleItem must match
            if ($item->getSaleItem() !== $shipmentItem->getSaleItem()) {
                $this
                    ->context
                    ->buildViolation($constraint->sale_item_and_shipment_item_miss_match)
                    ->setInvalidValue($shipmentItem)
                    ->atPath('shipmentItem')
                    ->addViolation();

                return;
            }

            // InvoiceLine's quantity can't be greater than the related ShipmentItem's quantity
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
        } else {

        }

        // The Sale of the InvoiceLine's SaleItem must match the Sale of the SaleItem's Invoice
        if ($item->getSaleItem()->getSale() !== $item->getInvoice()->getSale()) {
            $this
                ->context
                ->buildViolation($constraint->sale_and_invoice_miss_match)
                ->setInvalidValue($item->getSaleItem())
                ->atPath('saleItem')
                ->addViolation();

            return;
        }

        // InvoiceLine's quantity can't be greater than the invoiceable quantity
        $available = InvoiceUtil::calculateMaxCreditQuantity($item->getSaleItem());
        if ($item->getQuantity() > $available) {
            $this
                ->context
                ->buildViolation($constraint->quantity_is_greater_than_invoiceable, [
                    '%max%' => $available,
                ])
                ->setInvalidValue($shipmentItem)
                ->atPath('quantity')
                ->addViolation();

            return;
        }
    }
}
