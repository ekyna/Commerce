<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilder;
use Ekyna\Component\Commerce\Document\Model as Document;
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
     * @param SaleFactoryInterface        $factory
     * @param InvoiceCalculatorInterface  $invoiceCalculator
     * @param ShipmentCalculatorInterface $shipmentCalculator
     * @param PhoneNumberUtil             $phoneNumberUtil
     */
    public function __construct(
        SaleFactoryInterface $factory,
        InvoiceCalculatorInterface $invoiceCalculator,
        ShipmentCalculatorInterface $shipmentCalculator,
        PhoneNumberUtil $phoneNumberUtil = null
    ) {
        parent::__construct($phoneNumberUtil);

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
    public function buildGoodLine(Common\SaleItemInterface $item, Document\DocumentInterface $invoice, $recurse = true)
    {
        /** @var Invoice\InvoiceInterface $invoice */
        $line = null;

        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
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
                    ->setType(Document\DocumentLineTypes::TYPE_GOOD)
                    ->setSaleItem($item)
                    ->setDesignation($item->getDesignation())
                    ->setDescription($item->getDescription())
                    ->setReference($item->getReference());

                $invoice->addLine($line);
            }

            if (!$item->isCompound()) {
                $expected = 0;
                if (Invoice\InvoiceTypes::isInvoice($invoice)) {
                    // Invoice case
                    $expected = $available = $this->invoiceCalculator->calculateInvoiceableQuantity($line);
                } elseif (null !== $invoice->getShipment()) {
                    // Credit case
                    $available = $this->invoiceCalculator->calculateCreditableQuantity($line);
                } else {
                    // Cancel case
                    $available = $this->invoiceCalculator->calculateCancelableQuantity($line);
                }

                if (0 < $available) {
                    // Set available and expected quantity
                    $line->setAvailable($available);
                    $line->setExpected($expected);

                    if (Invoice\InvoiceTypes::isInvoice($invoice) && null === $invoice->getId()) {
                        // Set default quantity for new non return shipment items
                        $line->setQuantity(min($expected, $available));
                    }
                } else {
                    // Remove unexpected line
                    $invoice->removeLine($line);
                    $line = null;
                }
            }
        }

        if ($recurse && $item->hasChildren()) {
            if (null !== $line && $item->isCompound()) {
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
                    // Set expected and available quantity
                    $line->setExpected($expected);
                    $line->setAvailable($available);

                    if (Invoice\InvoiceTypes::isInvoice($invoice) && null === $invoice->getId()) {
                        // Set default quantity for new non credit invoice lines
                        $line->setQuantity(min($expected, $available));
                    }
                } else {
                    // Remove unexpected line
                    $invoice->removeLine($line);
                    $item = null;
                }
            } else {
                foreach ($item->getChildren() as $childLine) {
                    $this->buildGoodLine($childLine, $invoice);
                }
            }
        }

        return $line;
    }

    /**
     * @inheritdoc
     *
     * @param Invoice\InvoiceLineInterface $line
     */
    protected function postBuildLine(Document\DocumentLineInterface $line)
    {
        /** @var Invoice\InvoiceInterface $invoice */
        $invoice = $line->getDocument();

        if (Invoice\InvoiceTypes::isInvoice($invoice)) {
            // Invoice case
            $available = $this->invoiceCalculator->calculateInvoiceableQuantity($line);
        } elseif (null !== $invoice->getShipment()) {
            // Credit case
            $available = $this->invoiceCalculator->calculateCreditableQuantity($line);
        } else {
            // Cancel case
            $available = $this->invoiceCalculator->calculateCancelableQuantity($line);
        }

        if (0 < $available) {
            $line->setExpected($available);
            $line->setAvailable($available);

            /** @var Invoice\InvoiceLineInterface $line */
            if (Invoice\InvoiceTypes::isInvoice($invoice)) {
                $line->setQuantity($available);
            } elseif (Invoice\InvoiceTypes::isCredit($invoice)) {
                $line->setQuantity(0);
            }
        } else {
            $invoice->removeLine($line);
        }
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
