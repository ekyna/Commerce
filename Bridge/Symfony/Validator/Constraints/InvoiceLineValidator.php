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

        $subject = null;
        $invoice = $line->getInvoice();

        // Good line
        if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
            if (null === $subject = $line->getSaleItem()) {
                $this
                    ->context
                    ->buildViolation($constraint->null_sale_item)
                    ->setInvalidValue(null)
                    ->atPath('saleItem')
                    ->addViolation();

                return;
            }

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
                    $message = InvoiceTypes::isInvoice($invoice)
                        ? $constraint->shipped_miss_match
                        : $constraint->returned_miss_match;

                    $this
                        ->context
                        ->buildViolation($message, [
                            '%qty%' => $shipmentItem->getQuantity(),
                        ])
                        ->setInvalidValue($line->getQuantity())
                        ->atPath('quantity')
                        ->addViolation();

                    return;
                }
            }
        }

        // Discount line
        elseif ($line->getType() === DocumentLineTypes::TYPE_DISCOUNT) {
            if (null === $subject = $line->getSaleAdjustment()) {
                $this
                    ->context
                    ->buildViolation($constraint->null_sale_adjustment)
                    ->setInvalidValue(null)
                    ->atPath('saleAdjustment')
                    ->addViolation();

                return;
            }

        }

        // Shipment line
        elseif ($line->getType() === DocumentLineTypes::TYPE_SHIPMENT) {
            $subject = $invoice->getSale();
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

        if (InvoiceTypes::isInvoice($invoice)) {
            // Invoice case
            $max = $this->invoiceCalculator->calculateInvoiceableQuantity($subject, $invoice);
            $message = $constraint->invoiceable_overflow;
        } else {
            // Credit case
            $max = $this->invoiceCalculator->calculateCreditableQuantity($subject, $invoice);
            $message = $constraint->creditable_overflow;
        }

        // Check quantity integrity
        if ($max < $line->getQuantity()) {
            $this
                ->context
                ->buildViolation($message, ['%max%' => $max])
                ->setInvalidValue($line->getQuantity())
                ->atPath('quantity')
                ->addViolation();

            return;
        }
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
