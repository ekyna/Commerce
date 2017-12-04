<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Validator\Constraints;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
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
     * @var InvoiceCalculatorInterface
     */
    private $invoiceCalculator;

    /**
     * Constructor.
     *
     * @param InvoiceCalculatorInterface $calculator
     */
    public function __construct(InvoiceCalculatorInterface $calculator)
    {
        $this->invoiceCalculator = $calculator;
    }

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

                return;
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

                return;
            }
        }

        $invoice = $line->getInvoice();

        // Invoice case
        if (InvoiceTypes::isInvoice($invoice)) {
            // If invoice is linked to a shipment
            if (null !== $shipment = $invoice->getShipment()) {
                // Check that a matching shipment item exists
                if (null === $shipmentItem = $this->findMatchingShipmentItem($line, $shipment)) {
                    $this
                        ->context
                        ->buildViolation($constraint->hierarchy_integrity)
                        ->addViolation();

                    return;
                }

                // Check that quantities equals
                if ($shipmentItem->getQuantity() != $line->getQuantity()) {
                    $this
                        ->context
                        ->buildViolation($constraint->shipped_miss_match, [
                            '%qty%' => $shipmentItem->getQuantity(),
                        ])
                        ->setInvalidValue($line->getQuantity())
                        ->atPath('quantity')
                        ->addViolation();

                    return;
                }
            }

            // Check invoiceable quantity
            $max = $this->invoiceCalculator->calculateInvoiceableQuantity($line);
            if ($max < $line->getQuantity()) {
                $this
                    ->context
                    ->buildViolation($constraint->invoiceable_overflow, [
                        '%max%' => $max,
                    ])
                    ->setInvalidValue($line->getQuantity())
                    ->atPath('quantity')
                    ->addViolation();
            }

            return;
        }

        // Credit case
        if (null !== $shipment = $invoice->getShipment()) {
            // Check that a matching shipment item exists
            if (null === $shipmentItem = $this->findMatchingShipmentItem($line, $shipment)) {
                $this
                    ->context
                    ->buildViolation($constraint->hierarchy_integrity)
                    ->addViolation();

                return;
            }

            // Check that quantities equals
            if ($shipmentItem->getQuantity() != $line->getQuantity()) {
                $this
                    ->context
                    ->buildViolation($constraint->returned_miss_match, [
                        '%qty%' => $shipmentItem->getQuantity(),
                    ])
                    ->setInvalidValue($line->getQuantity())
                    ->atPath('quantity')
                    ->addViolation();

                return;
            }

            // Check creditable quantity
            $max = $this->invoiceCalculator->calculateCreditableQuantity($line);
            if ($max < $line->getQuantity()) {
                $this
                    ->context
                    ->buildViolation($constraint->creditable_overflow, [
                        '%max%' => $max,
                    ])
                    ->setInvalidValue($line->getQuantity())
                    ->atPath('quantity')
                    ->addViolation();
            }

            return;
        }

        // Cancel case

        // Check invoiceable quantity
        $max = $this->invoiceCalculator->calculateCancelableQuantity($line);
        if ($max < $line->getQuantity()) {
            $this
                ->context
                ->buildViolation($constraint->cancelable_overflow, [
                    '%max%' => $max,
                ])
                ->setInvalidValue($line->getQuantity())
                ->atPath('quantity')
                ->addViolation();
        }

        return;
    }

    /**
     * Finds the shipment item matching the invoice line.
     *
     * @param InvoiceLineInterface $line
     * @param ShipmentInterface    $shipment
     *
     * @return \Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface|null
     */
    private function findMatchingShipmentItem(InvoiceLineInterface $line, ShipmentInterface $shipment)
    {
        $saleItem = $line->getSaleItem();

        foreach ($shipment->getItems() as $shipmentItem) {
            if ($saleItem === $shipmentItem->getSaleItem()) {
                return $shipmentItem;
            }
        }

        return null;
    }
}
