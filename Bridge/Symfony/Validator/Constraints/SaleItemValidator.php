<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\ValidationFailedException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
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
    protected InvoiceSubjectCalculatorInterface $invoiceCalculator;
    protected ShipmentSubjectCalculatorInterface $shipmentCalculator;

    public function __construct(
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        ShipmentSubjectCalculatorInterface $shipmentCalculator
    ) {
        $this->invoiceCalculator = $invoiceCalculator;
        $this->shipmentCalculator = $shipmentCalculator;
    }

    /**
     * @inheritDoc
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        if (!$value instanceof SaleItemInterface) {
            throw new UnexpectedTypeException($value, SaleItemInterface::class);
        }
        if (!$constraint instanceof SaleItem) {
            throw new UnexpectedTypeException($constraint, SaleItem::class);
        }

        try {
            $this->checkPrivacyIntegrity($value, $constraint);
            $this->checkInvoiceIntegrity($value, $constraint);
            $this->checkShipmentIntegrity($value, $constraint);
        } catch (ValidationFailedException $e) {
            return;
        }
    }

    /**
     * Checks the sale item privacy integrity.
     *
     * @throws ValidationFailedException
     */
    protected function checkPrivacyIntegrity(SaleItemInterface $item, SaleItem $constraint): void
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
     * @throws ValidationFailedException
     */
    protected function checkInvoiceIntegrity(SaleItemInterface $item, SaleItem $constraint): void
    {
        $sale = $item->getSale();
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return;
        }

        if (empty($sale->getInvoices()->toArray())) {
            return;
        }

        $min = $this->invoiceCalculator->calculateInvoicedQuantity($item)
             - $this->invoiceCalculator->calculateCreditedQuantity($item);

        // TODO Use packaging format
        if (0 < $min && $min > $item->getTotalQuantity()) {
            $this
                ->context
                ->buildViolation($constraint->quantity_is_lower_than_invoiced, [
                    '%min%' => $min,
                ])
                ->atPath('quantity')
                ->addViolation();

            throw new ValidationFailedException();
        }
    }

    /**
     * Checks that the sale item quantity is greater than or equals the shipped quantity.
     *
     * @throws ValidationFailedException
     */
    protected function checkShipmentIntegrity(SaleItemInterface $item, SaleItem $constraint): void
    {
        $sale = $item->getSale();
        if (!$sale instanceof Shipment\ShipmentSubjectInterface) {
            return;
        }

        if (empty($sale->getShipments()->toArray())) {
            return;
        }

        $min = $this->shipmentCalculator->calculateShippedQuantity($item)
             - $this->shipmentCalculator->calculateReturnedQuantity($item);

        // TODO Use packaging format
        if (0 < $min && $min > $item->getTotalQuantity()) {
            $this
                ->context
                ->buildViolation($constraint->quantity_is_lower_than_shipped, [
                    '%min%' => $min,
                ])
                ->setInvalidValue($item->getQuantity())
                ->atPath('quantity')
                ->addViolation();

            throw new ValidationFailedException();
        }
    }
}
