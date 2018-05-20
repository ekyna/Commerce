<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilder;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculatorInterface;
use libphonenumber\PhoneNumberUtil;

/**
 * Class InvoiceBuilder
 * @package Ekyna\Component\Commerce\Invoice\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceBuilder extends DocumentBuilder implements InvoiceBuilderInterface
{
    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;

    /**
     * @var InvoiceCalculatorInterface
     */
    private $invoiceCalculator;

    /**
     * @var ShipmentCalculatorInterface
     */
    private $shipmentCalculator;


    /**
     * Constructor.
     *
     * @param ContextProviderInterface    $contextProvider
     * @param SaleFactoryInterface        $factory
     * @param InvoiceCalculatorInterface  $invoiceCalculator
     * @param ShipmentCalculatorInterface $shipmentCalculator
     * @param PhoneNumberUtil             $phoneNumberUtil
     */
    public function __construct(
        ContextProviderInterface $contextProvider,
        SaleFactoryInterface $factory,
        InvoiceCalculatorInterface $invoiceCalculator,
        ShipmentCalculatorInterface $shipmentCalculator,
        PhoneNumberUtil $phoneNumberUtil = null
    ) {
        parent::__construct($contextProvider, $phoneNumberUtil);

        $this->saleFactory = $factory;
        $this->invoiceCalculator = $invoiceCalculator;
        $this->shipmentCalculator = $shipmentCalculator;
    }

    /**
     * Returns the sale factory.
     *
     * @return SaleFactoryInterface
     */
    public function getSaleFactory()
    {
        return $this->saleFactory;
    }

    /**
     * Returns the invoice calculator.
     *
     * @return InvoiceCalculatorInterface
     */
    public function getInvoiceCalculator()
    {
        return $this->invoiceCalculator;
    }

    /**
     * @inheritdoc
     */
    public function buildGoodLine(Common\SaleItemInterface $item, Document\DocumentInterface $invoice)
    {
        if (!$invoice instanceof Invoice\InvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceInterface::class);
        }

        // Compound with only private children
        if ($item->isCompound()) {
            $available = $expected = null;
            foreach ($item->getChildren() as $childItem) {
                if (null !== $childLine = $this->buildGoodLine($childItem, $invoice)) {
                    $saleItemQty = $childItem->getQuantity();

                    $a = $childLine->getAvailable() / $saleItemQty;
                    if (null === $available || $available > $a) {
                        $available = $a;
                    }

                    $e = $childLine->getExpected() / $saleItemQty;
                    if (null === $expected || $expected > $e) {
                        $expected = $e;
                    }
                }
            }

            if (0 < $available) {
                return $this->findOrCreateGoodLine($invoice, $item, $available, $expected);
            }

            return null;
        }

        $line = null;

        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            if (Invoice\InvoiceTypes::isInvoice($invoice)) {
                // Invoice case
                $available = $this->invoiceCalculator->calculateInvoiceableQuantity($item, $invoice);
            } else {
                // Credit case
                $available = $this->invoiceCalculator->calculateCreditableQuantity($item, $invoice);
            }

            if (0 < $available) {
                $expected = null;
                if (Invoice\InvoiceTypes::isInvoice($invoice)) {
                    $expected = min($available, $this->shipmentCalculator->calculateShippedQuantity($item));
                }

                $line = $this->findOrCreateGoodLine($invoice, $item, $available, $expected);
            }
        }

        // Build children
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $childLine) {
                $this->buildGoodLine($childLine, $invoice);
            }
        }

        return $line;
    }

    /**
     * @inheritDoc
     */
    public function buildDiscountLine(Common\SaleAdjustmentInterface $adjustment, Document\DocumentInterface $invoice)
    {
        if (!$invoice instanceof Invoice\InvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceInterface::class);
        }

        $line = null;
        $expected = null;
        if (Invoice\InvoiceTypes::isInvoice($invoice)) {
            // Invoice case
            $expected = $available = $this->invoiceCalculator->calculateInvoiceableQuantity($adjustment, $invoice);
        } else {
            // Credit case
            $available = $this->invoiceCalculator->calculateCreditableQuantity($adjustment, $invoice);
        }

        if (0 < $available) {
            /** @var Invoice\InvoiceLineInterface $line */
            $line = parent::buildDiscountLine($adjustment, $invoice);
            $line
                ->setAvailable($available)
                ->setExpected($expected)
                ->setQuantity(1);
        }

        return $line;
    }

    /**
     * @inheritDoc
     */
    public function buildShipmentLine(Document\DocumentInterface $invoice)
    {
        if (!$invoice instanceof Invoice\InvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceInterface::class);
        }

        $line = null;
        $expected = null;
        $sale = $invoice->getSale();
        if (Invoice\InvoiceTypes::isInvoice($invoice)) {
            // Invoice case
            $expected = $available = $this->invoiceCalculator->calculateInvoiceableQuantity($sale, $invoice);
        } else {
            // Credit case
            $available = $this->invoiceCalculator->calculateCreditableQuantity($sale, $invoice);
        }

        if (0 < $available) {
            /** @var Invoice\InvoiceLineInterface $line */
            $line = parent::buildShipmentLine($invoice);
            $line
                ->setAvailable($available)
                ->setExpected($expected)
                ->setQuantity(max(1, $available));
        }

        return $line;
    }

    /**
     * Finds or create the invoice line.
     *
     * @param Invoice\InvoiceInterface $invoice
     * @param Common\SaleItemInterface $item
     * @param float                    $available
     * @param float                    $expected
     *
     * @return Invoice\InvoiceLineInterface
     */
    public function findOrCreateGoodLine(
        Invoice\InvoiceInterface $invoice,
        Common\SaleItemInterface $item,
        $available,
        $expected = null
    ) {
        $line = null;

        if (0 >= $available) {
            return $line;
        }

        // Existing line lookup
        foreach ($invoice->getLinesByType(Document\DocumentLineTypes::TYPE_GOOD) as $invoiceLine) {
            if ($invoiceLine->getSaleItem() === $item) {
                $line = $invoiceLine;
            }
        }

        // Not found, create it
        if (null === $line) {
            $line = $this->createLine($invoice);
            $line
                ->setInvoice($invoice)
                ->setType(Document\DocumentLineTypes::TYPE_GOOD)
                ->setSaleItem($item)
                ->setDesignation($item->getDesignation())
                ->setDescription($item->getDescription())
                ->setReference($item->getReference());
        }

        // Set available and expected quantity
        $line->setAvailable($available);
        $line->setExpected($expected);

        if (Invoice\InvoiceTypes::isInvoice($invoice) && null === $invoice->getId()) {
            // Set default quantity for new non return shipment items
            $line->setQuantity(min($expected, $available));
        }

        return $line;
    }

    /**
     * @inheritdoc
     */
    protected function createLine(Document\DocumentInterface $invoice)
    {
        /** @var Invoice\InvoiceInterface $invoice */
        return $this->saleFactory->createLineForInvoice($invoice);
    }
}
