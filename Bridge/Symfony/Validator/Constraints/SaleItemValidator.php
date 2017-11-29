<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\ValidationFailedException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
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

        try {
            $this->checkPrivacyIntegrity($item, $constraint);
            $this->checkInvoiceIntegrity($item, $constraint);
            $this->checkShipmentIntegrity($item, $constraint);
        } catch (ValidationFailedException $e) {
            return;
        }
    }

    /**
     * Checks the sale item privacy integrity.
     *
     * @param SaleItemInterface $item
     * @param SaleItem          $constraint
     *
     * @throws ValidationFailedException
     */
    protected function checkPrivacyIntegrity(SaleItemInterface $item, SaleItem $constraint)
    {
        $parent = $item->getParent();

        if ($item->isPrivate()) {
            if (null === $parent) {
                // Level zero items must be public
                $this
                    ->context
                    ->buildViolation($constraint->root_item_cant_be_private)
                    ->atPath('private')
                    ->addViolation();

                throw new ValidationFailedException();
            } elseif ($item->getTaxGroup() !== $parent->getTaxGroup()) {
                // Tax group must match parent's one
                $this
                    ->context
                    ->buildViolation($constraint->tax_group_integrity)
                    ->atPath('taxGroup')
                    ->addViolation();

                throw new ValidationFailedException();
            }
        } elseif (null !== $parent && $parent->isPrivate()) {
            // Item with private parent must be private
            $this
                ->context
                ->buildViolation($constraint->privacy_integrity)
                ->atPath('private')
                ->addViolation();

            throw new ValidationFailedException();
        }
    }

    /**
     * Checks that the sale item quantity is greater than or equals the invoiced quantity.
     *
     * @param SaleItemInterface $item
     * @param SaleItem          $constraint
     *
     * @throws ValidationFailedException
     */
    protected function checkInvoiceIntegrity(SaleItemInterface $item, SaleItem $constraint)
    {
        $sale = $item->getSale();
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return;
        }

        $invoices = $sale->getInvoices()->toArray();

        if (empty($invoices)) {
            return;
        }

        $quantity = 0;

        /** @var Invoice\InvoiceInterface $invoice */
        foreach ($invoices as $invoice) {
            foreach ($invoice->getLines() as $line) {
                if ($line->getSaleItem() === $item) {
                    if (Invoice\InvoiceTypes::isCredit($invoice)) {
                        $quantity -= $line->getQuantity();
                    } else {
                        $quantity += $line->getQuantity();
                    }
                }
            }
        }

        if (0 < $quantity && $item->getTotalQuantity() < $quantity) {
            $this
                ->context
                ->buildViolation($constraint->quantity_is_lower_than_credited, [
                    '%max%' => $quantity,
                ])
                ->atPath('quantity')
                ->addViolation();

            throw new ValidationFailedException();
        }
    }

    /**
     * Checks that the sale item quantity is greater than or equals the shipped quantity.
     *
     * @param SaleItemInterface $item
     * @param SaleItem          $constraint
     *
     * @throws ValidationFailedException
     */
    protected function checkShipmentIntegrity(SaleItemInterface $item, SaleItem $constraint)
    {
        $sale = $item->getSale();
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return;
        }

        $shipments = $sale->getShipments()->toArray();

        if (empty($shipments)) {
            return;
        }

        $quantity = 0;

        /** @var Shipment\ShipmentInterface $shipment */
        foreach ($shipments as $shipment) {
            if (!Shipment\ShipmentStates::isStockableState($shipment->getState())) {
                continue;
            }

            foreach ($shipment->getItems() as $shipmentItem) {
                if ($shipmentItem->getSaleItem() === $item) {
                    if ($shipment->isReturn()) {
                        $quantity -= $shipmentItem->getQuantity();
                    } else {
                        $quantity += $shipmentItem->getQuantity();
                    }
                }
            }
        }

        if (0 < $quantity && $item->getTotalQuantity() < $quantity) {
            $this
                ->context
                ->buildViolation($constraint->quantity_is_lower_than_shipped, [
                    '%max%' => $quantity,
                ])
                ->setInvalidValue($item->getQuantity())
                ->atPath('quantity')
                ->addViolation();

            throw new ValidationFailedException();
        }
    }
}
