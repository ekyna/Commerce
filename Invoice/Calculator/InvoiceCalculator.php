<?php

namespace Ekyna\Component\Commerce\Invoice\Calculator;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Model\DocumentLineTypes;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;

/**
 * Class QuantityCalculator
 * @package Ekyna\Component\Commerce\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceCalculator implements InvoiceCalculatorInterface
{
    /**
     * @var ShipmentCalculatorInterface
     */
    protected $shipmentCalculator;


    /**
     * Sets the shipment calculator.
     *
     * @param ShipmentCalculatorInterface $calculator
     */
    public function setShipmentCalculator(ShipmentCalculatorInterface $calculator)
    {
        $this->shipmentCalculator = $calculator;
    }

    /**
     * @inheritDoc
     */
    public function calculateInvoiceableQuantity(Invoice\InvoiceLineInterface $line)
    {
        if (!Invoice\InvoiceTypes::isInvoice($line->getInvoice())) {
            throw new LogicException(sprintf("Expected invoice with type '%s'.", Invoice\InvoiceTypes::TYPE_INVOICE));
        }

        if (null === $sale = $line->getSale()) {
            throw new LogicException("Invoice's sale must be set.");
        }

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceSubjectInterface::class);
        }

        // Good line case
        if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
            if (null === $saleItem = $line->getSaleItem()) {
                throw new LogicException("Invoice line's sale item must be set.");
            }

            // Quantity = Sold - Invoiced (ignoring current invoice)
            return $saleItem->getTotalQuantity()
                - $this->calculateInvoicedQuantity($saleItem, $line->getInvoice());
        }

        // Discount line case
        if ($line->getType() === DocumentLineTypes::TYPE_DISCOUNT) {
            if (null === $adjustment = $line->getSaleAdjustment()) {
                throw new LogicException("Invoice line's sale adjustment must be set.");
            }

            // Discounts must be dispatched into all invoices
            return 1;
        }

        // Shipment line case
        if ($line->getType() === DocumentLineTypes::TYPE_SHIPMENT) {
            // Shipment must be invoiced once
            $quantity = 1;

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current invoice
                if ($invoice === $line->getInvoice() || !Invoice\InvoiceTypes::isInvoice($invoice)) {
                    continue;
                }

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_SHIPMENT) as $invoiceLine) {
                    $quantity -= $invoiceLine->getQuantity();
                    break;
                }
            }

            if (0 > $quantity) $quantity = 0;

            return $quantity;
        }

        throw new InvalidArgumentException("Unexpected line type '{$line->getType()}'.");
    }

    /**
     * @inheritDoc
     */
    public function calculateCreditableQuantity(Invoice\InvoiceLineInterface $line)
    {
        if (!Invoice\InvoiceTypes::isCredit($line->getInvoice())) {
            throw new LogicException(sprintf("Expected invoice with type '%s'.", Invoice\InvoiceTypes::TYPE_CREDIT));
        }

        if (null === $sale = $line->getSale()) {
            throw new LogicException("Invoice's sale must be set.");
        }

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceSubjectInterface::class);
        }

        // TODO assert shipment linked

        // Good line case
        if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
            if (null === $saleItem = $line->getSaleItem()) {
                throw new LogicException("Invoice line's sale item must be set.");
            }

            // Quantity = Returned - Credited (ignoring current credit)
            return $this->shipmentCalculator->calculateReturnedQuantity($saleItem)
                - $this->calculateCreditedQuantity($saleItem, $line->getInvoice());
        }

        // Discount line case
        if ($line->getType() === DocumentLineTypes::TYPE_DISCOUNT) {
            if (null === $adjustment = $line->getSaleAdjustment()) {
                throw new LogicException("Invoice line's sale adjustment must be set.");
            }

            // Discounts must be dispatched into all invoices
            return 1;
        }

        // Shipment line case
        if ($line->getType() === DocumentLineTypes::TYPE_SHIPMENT) {
            // Shipment can't be credited (but canceled)
            return 0;
        }

        throw new InvalidArgumentException("Unexpected line type '{$line->getType()}'.");
    }

    /**
     * @inheritDoc
     */
    public function calculateCancelableQuantity(Invoice\InvoiceLineInterface $line)
    {
        if (!Invoice\InvoiceTypes::isCredit($line->getInvoice())) {
            throw new LogicException(sprintf("Expected invoice with type '%s'.", Invoice\InvoiceTypes::TYPE_CREDIT));
        }

        if (null === $sale = $line->getSale()) {
            throw new LogicException("Invoice's sale must be set.");
        }

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceSubjectInterface::class);
        }

        // TODO assert no shipment linked

        // Good line case
        if ($line->getType() === DocumentLineTypes::TYPE_GOOD) {
            if (null === $saleItem = $line->getSaleItem()) {
                throw new LogicException("Invoice line's sale item must be set.");
            }

            // Quantity = Invoiced - Shipped - Canceled (ignoring current credit)
            return $this->calculateInvoicedQuantity($saleItem)
                - $this->shipmentCalculator->calculateShippedQuantity($saleItem)
                - $this->calculateCanceledQuantity($saleItem, $line->getInvoice());
        }

        // Discount line case
        if ($line->getType() === DocumentLineTypes::TYPE_DISCOUNT) {
            if (null === $adjustment = $line->getSaleAdjustment()) {
                throw new LogicException("Invoice line's sale adjustment must be set.");
            }

            // Discounts must be dispatched into all invoices
            return 1;
        }

        // Shipment line case
        if ($line->getType() === DocumentLineTypes::TYPE_SHIPMENT) {
            // Shipment can be credited once
            $quantity = 0;

            foreach ($sale->getInvoices() as $invoice) {
                // Ignore the current invoice
                if ($invoice === $line->getInvoice()) {
                    continue;
                }

                $credit = Invoice\InvoiceTypes::isCredit($invoice);

                foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_SHIPMENT) as $invoiceLine) {
                    $quantity += $credit ? -$invoiceLine->getQuantity() : $invoiceLine->getQuantity();
                }
            }

            if (0 > $quantity) $quantity = 0;
            if (1 < $quantity) $quantity = 1;

            return $quantity;
        }

        throw new InvalidArgumentException("Unexpected line type '{$line->getType()}'.");
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoicedQuantity(Common\SaleItemInterface $item, Invoice\InvoiceInterface $ignore = null)
    {
        $sale = $item->getSale();

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getInvoices() as $invoice) {
            if (null !== $ignore && $invoice === $ignore) {
                continue;
            }

            if (Invoice\InvoiceTypes::isCredit($invoice)) {
                continue;
            }

            foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
                if ($line->getSaleItem() === $item) {
                    $quantity += $line->getQuantity();
                }
            }
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditedQuantity(Common\SaleItemInterface $item, Invoice\InvoiceInterface $ignore = null)
    {
        $sale = $item->getSale();

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getInvoices() as $invoice) {
            if (null !== $ignore && $invoice === $ignore) {
                continue;
            }

            if (!(Invoice\InvoiceTypes::isCredit($invoice) && null !== $invoice->getShipment())) {
                continue;
            }

            foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
                if ($line->getSaleItem() === $item) {
                    $quantity += $line->getQuantity();
                }
            }
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateCanceledQuantity(Common\SaleItemInterface $item, Invoice\InvoiceInterface $ignore = null)
    {
        $sale = $item->getSale();

        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            return 0;
        }

        $quantity = 0;

        foreach ($sale->getInvoices() as $invoice) {
            if (null !== $ignore && $invoice === $ignore) {
                continue;
            }

            if (!(Invoice\InvoiceTypes::isCredit($invoice) && null === $invoice->getShipment())) {
                continue;
            }

            foreach ($invoice->getLinesByType(DocumentLineTypes::TYPE_GOOD) as $line) {
                if ($line->getSaleItem() === $item) {
                    $quantity += $line->getQuantity();
                }
            }
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function calculateInvoiceTotal(Invoice\InvoiceSubjectInterface $subject)
    {
        $total = .0;

        foreach ($subject->getInvoices() as $invoice) {
            if (Invoice\InvoiceTypes::isInvoice($invoice)) {
                $total += $invoice->getGrandTotal();
            }
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateCreditTotal(Invoice\InvoiceSubjectInterface $subject)
    {
        $total = .0;

        foreach ($subject->getInvoices() as $invoice) {
            if (Invoice\InvoiceTypes::isCredit($invoice)) {
                $total += $invoice->getGrandTotal();
            }
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function calculateCanceledTotal(Invoice\InvoiceSubjectInterface $subject)
    {
        $total = .0;

        foreach ($subject->getInvoices() as $invoice) {
            if (Invoice\InvoiceTypes::isCredit($invoice) && null === $invoice->getShipment()) {
                $total += $invoice->getGrandTotal();
            }
        }

        return $total;
    }

    /**
     * @inheritdoc
     */
    public function buildInvoiceQuantityMap(Invoice\InvoiceSubjectInterface $subject)
    {
        $quantities = [];

        if ($subject instanceof Common\SaleInterface) {
            foreach ($subject->getItems() as $item) {
                $this->buildSaleItemQuantities($item, $quantities);
            }
        }

        return $quantities;
    }

    /**
     * Builds the sale item quantities recursively.
     *
     * @param Common\SaleItemInterface $item
     * @param array                    $quantities
     */
    private function buildSaleItemQuantities(Common\SaleItemInterface $item, array &$quantities)
    {
        if (!$item->isCompound()) {
            $quantities[$item->getId()] = [
                'sold'     => $item->getTotalQuantity(),
                'invoiced' => $this->calculateInvoicedQuantity($item),
                'credited' => $this->calculateCreditedQuantity($item),
                'canceled' => $this->calculateCanceledQuantity($item),
            ];
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildSaleItemQuantities($child, $quantities);
            }
        }
    }
}
