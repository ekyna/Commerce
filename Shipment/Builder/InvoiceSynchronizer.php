<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Builder;

use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Builder\InvoiceBuilderInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class InvoiceSynchronizer
 * @package Ekyna\Component\Commerce\Shipment\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSynchronizer implements InvoiceSynchronizerInterface
{
    use Common\LockCheckerAwareTrait;

    protected InvoiceBuilderInterface           $invoiceBuilder;
    protected InvoiceSubjectCalculatorInterface $invoiceCalculator;
    protected DocumentCalculatorInterface       $documentCalculator;
    protected PersistenceHelperInterface        $persistenceHelper;

    public function __construct(
        InvoiceBuilderInterface           $invoiceBuilder,
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        DocumentCalculatorInterface       $documentCalculator,
        PersistenceHelperInterface        $persistenceHelper
    ) {
        $this->invoiceBuilder = $invoiceBuilder;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->documentCalculator = $documentCalculator;
        $this->persistenceHelper = $persistenceHelper;
    }

    public function synchronize(Shipment\ShipmentInterface $shipment, bool $force = false): void
    {
        // Abort if auto invoicing is disabled
        // (We do not remove linked invoice)
        if (!$shipment->isAutoInvoice()) {
            return;
        }

        // Shipment sale must be set
        /** @var Invoice\InvoiceSubjectInterface|Common\SaleInterface $sale */
        if (null === $sale = $shipment->getSale()) {
            throw new LogicException("Shipment's sale must be set at this point.");
        }

        $invoice = $shipment->getInvoice();

        // Abort if invoice has an id.
        if ($invoice && $invoice->getId() && !($force && !$this->lockChecker->isLocked($invoice))) {
            return;
        }

        // Abort if sale is sample, shipment is removed or shipment not in stockable state
        if (
            $sale->isSample()
            || $this->persistenceHelper->isScheduledForRemove($shipment)
            || !Shipment\ShipmentStates::isStockableState($shipment, false)
        ) {
            if ($invoice) {
                $this->removeInvoice($invoice);
            }

            return;
        }

        if (null === $invoice) {
            $invoice = $this->invoiceBuilder->getFactoryHelper()->createInvoiceForSale($sale);
            $invoice->setSale($sale);
            $invoice->setShipment($shipment);
            $invoice->setCredit($shipment->isReturn());
        }

        $this->checkShipmentInvoice($invoice);

        $changed = $this->purgeShipmentInvoice($invoice);

        $changed = $this->feedShipmentInvoice($invoice) || $changed;

        // Remove (and don't persist) empty invoices
        if (0 == count($invoice->getLinesByType(Document\DocumentLineTypes::TYPE_GOOD))) {
            $this->removeInvoice($invoice);

            return;
        }

        if ($changed) {
            $this->persistInvoice($invoice);
        }
    }

    /**
     * Persists the invoice.
     *
     * @param Invoice\InvoiceInterface $invoice
     */
    private function persistInvoice(Invoice\InvoiceInterface $invoice)
    {
        $this->persistenceHelper->persistAndRecompute($invoice, true);

        foreach ($invoice->getLines() as $line) {
            $this->persistenceHelper->persistAndRecompute($line, true);
        }

        // Persist the shipment <-> invoice relation, without scheduling event.
        $this->persistenceHelper->persistAndRecompute($invoice->getShipment(), false);
    }

    /**
     * Removes the invoice.
     *
     * @param Invoice\InvoiceInterface $invoice
     */
    private function removeInvoice(Invoice\InvoiceInterface $invoice)
    {
        // Never remove an existing invoice
        if ($invoice->getId()) {
            return;
        }

        $invoice->setShipment(null);
        $invoice->setSale(null);

        $this->persistenceHelper->remove($invoice, false);
    }

    /**
     * Checks that the given invoice is correctly configured for shipment synchronization.
     *
     * @param Invoice\InvoiceInterface $invoice
     *
     * @throws LogicException
     */
    private function checkShipmentInvoice(Invoice\InvoiceInterface $invoice)
    {
        if (null === $shipment = $invoice->getShipment()) {
            throw new LogicException("Invoice's shipment must be set at this point.");
        }

        // Check sale integrity
        if ($shipment->getSale() !== $sale = $invoice->getSale()) {
            throw new LogicException('Shipment/Invoice sale miss match.');
        }

        // Sale must be an invoice subject
        if (!$sale instanceof Invoice\InvoiceSubjectInterface) {
            throw new UnexpectedTypeException($sale, Invoice\InvoiceSubjectInterface::class);
        }

        // Check shipment/invoice types integrity.
        if ($shipment->isReturn() && !$invoice->isCredit()) {
            throw new LogicException('Invoice should not be associated with Return.');
        } elseif (!$shipment->isReturn() && $invoice->isCredit()) {
            throw new LogicException('Credit should not be associated with Shipment.');
        }
    }

    /**
     * Removes the invoice unexpected lines regarding his associated shipment.
     *
     * @param Invoice\InvoiceInterface $invoice
     *
     * @return bool Whether the invoice has been changed
     */
    private function purgeShipmentInvoice(Invoice\InvoiceInterface $invoice): bool
    {
        $changed = false;

        $shipment = $invoice->getShipment();

        // Remove unexpected good lines
        /** @var Invoice\InvoiceLineInterface $line */
        foreach ($invoice->getLinesByType(Document\DocumentLineTypes::TYPE_GOOD) as $line) {
            foreach ($shipment->getItems() as $shipmentItem) {
                if ($line->getSaleItem() === $shipmentItem->getSaleItem()) {
                    continue 2; // Shipment item found -> next invoice line
                }
            }

            // Shipment item not found -> remove line
            $invoice->removeLine($line);

            $this->persistenceHelper->remove($line, false);

            $changed = true;
        }

        $sale = $invoice->getSale();

        // Remove unexpected discount lines
        foreach ($invoice->getLinesByType(Document\DocumentLineTypes::TYPE_DISCOUNT) as $line) {
            foreach ($sale->getAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT) as $saleAdjustment) {
                if ($line->getSaleAdjustment() === $saleAdjustment) {
                    continue 2; // Sale adjustment found -> next invoice line
                }
            }

            // Sale adjustment not found -> remove line
            $invoice->removeLine($line);

            $this->persistenceHelper->remove($line, false);

            $changed = true;
        }

        // Remove unexpected shipment lines
        if (null === $sale->getShipmentMethod()) {
            foreach ($invoice->getLinesByType(Document\DocumentLineTypes::TYPE_SHIPMENT) as $line) {
                $invoice->removeLine($line);

                $this->persistenceHelper->remove($line, false);

                $changed = true;
            }
        }

        return $changed;
    }

    /**
     * Creates invoice expected lines regarding his associated shipment.
     *
     * @param Invoice\InvoiceInterface $invoice
     *
     * @return bool Whether the invoice has been changed
     */
    private function feedShipmentInvoice(Invoice\InvoiceInterface $invoice): bool
    {
        $changed = false;

        $shipment = $invoice->getShipment();

        $calculator = $this->invoiceBuilder->getInvoiceCalculator();

        // Add expected good lines
        /** @var Invoice\InvoiceLineInterface $line */
        foreach ($shipment->getItems() as $shipmentItem) {
            $saleItem = $shipmentItem->getSaleItem();

            $max = $shipment->isReturn()
                ? $calculator->calculateCreditableQuantity($saleItem, $invoice)
                : $calculator->calculateInvoiceableQuantity($saleItem, $invoice);

            if (0 < $quantity = min($max, $shipmentItem->getQuantity())) {
                $line = $this->invoiceBuilder->findOrCreateGoodLine($invoice, $saleItem);
                if ($line->getQuantity() !== $quantity) {
                    $line->setQuantity($quantity);
                    $changed = true;
                }
            } /*else {
                // TODO find and remove line ?
                $invoice->removeLine($line);
                $changed = true;
            }*/
        }

        if ($invoice->hasLineByType(Document\DocumentLineTypes::TYPE_GOOD)) {
            // Add expected discount lines
            $sale = $invoice->getSale();
            foreach ($sale->getAdjustments(Common\AdjustmentTypes::TYPE_DISCOUNT) as $saleAdjustment) {
                foreach ($invoice->getLinesByType(Document\DocumentLineTypes::TYPE_DISCOUNT) as $line) {
                    if ($saleAdjustment === $line->getSaleAdjustment()) {
                        continue 2; // Invoice line found -> next sale adjustment
                    }
                }

                // Invoice line not found -> create it
                $this->invoiceBuilder->buildDiscountLine($saleAdjustment, $invoice);

                $changed = true;
            }

            // Add expected shipment line
            if (null !== $sale->getShipmentMethod() && !$this->isShipmentAmountInvoiced($invoice)) {
                $this->invoiceBuilder->buildShipmentLine($invoice);
            }
        }

        return $changed;
    }

    /**
     * Returns whether the shipment amount is already invoiced.
     *
     * @param Invoice\InvoiceInterface $invoice
     *
     * @return bool
     */
    private function isShipmentAmountInvoiced(Invoice\InvoiceInterface $invoice): bool
    {
        /** @var Invoice\InvoiceSubjectInterface $sale */
        $sale = $invoice->getSale();

        // Abort if another invoice has a shipment line
        foreach ($sale->getInvoices() as $i) {
            if ($i === $invoice) {
                continue;
            }
            if ($i->hasLineByType(Document\DocumentLineTypes::TYPE_SHIPMENT)) {
                return true;
            }
        }

        return false;
    }
}
