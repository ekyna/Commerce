<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
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
    public function validate($line, Constraint $constraint)
    {
        if (null === $line) {
            return;
        }

        if (!$line instanceof InvoiceLineInterface) {
            throw new UnexpectedTypeException($line, InvoiceLineInterface::class);
        }
        if (!$constraint instanceof InvoiceLine) {
            throw new UnexpectedTypeException($constraint, InvoiceLine::class);
        }

        if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
            if (null === $line->getSaleItem()) {
                $this
                    ->context
                    ->buildViolation($constraint->null_sale_item)
                    ->setInvalidValue(null)
                    ->atPath('saleItem')
                    ->addViolation();
            }
        }

        if ($line->getType() !== DocumentLineTypes::TYPE_DISCOUNT) {
            if (empty($line->getDesignation())) {
                $this
                    ->context
                    ->buildViolation($constraint->empty_designation)
                    ->setInvalidValue($line->getDesignation())
                    ->atPath('designation')
                    ->addViolation();
            }
        }

        return;

//        TODO
//
//        // ShipmentItem vs SaleItem integrity
//        if (null !== $shipmentItem = $line->getShipmentItem()) {
//            // Shipment must be a return
//            if (!$shipmentItem->getShipment()->isReturn()) {
//                $this
//                    ->context
//                    ->buildViolation($constraint->shipment_is_not_return)
//                    ->setInvalidValue($shipmentItem)
//                    ->atPath('shipmentItem')
//                    ->addViolation();
//
//                return;
//            }
//
//            // Invoice's SaleItem and ShipmentItem's SaleItem must match
//            if ($line->getSaleItem() !== $shipmentItem->getSaleItem()) {
//                $this
//                    ->context
//                    ->buildViolation($constraint->sale_item_and_shipment_item_miss_match)
//                    ->setInvalidValue($shipmentItem)
//                    ->atPath('shipmentItem')
//                    ->addViolation();
//
//                return;
//            }
//
//            // InvoiceLine's quantity can't be greater than the related ShipmentItem's quantity
//            if ($line->getQuantity() > $shipmentItem->getQuantity()) {
//                $this
//                    ->context
//                    ->buildViolation($constraint->quantity_is_greater_than_returned, [
//                        '%max%' => $shipmentItem->getQuantity(),
//                    ])
//                    ->setInvalidValue($shipmentItem)
//                    ->atPath('quantity')
//                    ->addViolation();
//
//                return;
//            }
//        } else {
//
//        }
//
//        // The Sale of the InvoiceLine's SaleItem must match the Sale of the SaleItem's Invoice
//        if ($line->getSaleItem()->getSale() !== $line->getInvoice()->getSale()) {
//            $this
//                ->context
//                ->buildViolation($constraint->sale_and_invoice_miss_match)
//                ->setInvalidValue($line->getSaleItem())
//                ->atPath('saleItem')
//                ->addViolation();
//
//            return;
//        }
//
//        // InvoiceLine's quantity can't be greater than the invoiceable quantity
//        // TODO Use QuantityCalculatorInterface
//        $available = InvoiceUtil::calculateMaxCreditQuantity($line->getSaleItem());
//        if ($line->getQuantity() > $available) {
//            $this
//                ->context
//                ->buildViolation($constraint->quantity_is_greater_than_creditable, [
//                    '%max%' => $available,
//                ])
//                ->setInvalidValue($shipmentItem)
//                ->atPath('quantity')
//                ->addViolation();
//
//            return;
//        }
    }
}
