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
    public function buildGoodLine(Common\SaleItemInterface $item, Document\DocumentInterface $document, $recurse = true)
    {
        /** @var Invoice\InvoiceInterface $document */
        $line = null;

        // Skip compound with only public children
        if (!($item->isCompound() && !$item->hasPrivateChildren())) {
            // Existing line lookup
            foreach ($document->getLinesByType(Document\DocumentLineTypes::TYPE_GOOD) as $documentLine) {
                if ($documentLine->getSaleItem() === $item) {
                    $line = $documentLine;
                }
            }
            // Not found, create it
            if (null === $line) {
                $line = $this->createLine($document);
                $line
                    ->setType(Document\DocumentLineTypes::TYPE_GOOD)
                    ->setSaleItem($item)
                    ->setDesignation($item->getDesignation())
                    ->setDescription($item->getDescription())
                    ->setReference($item->getReference());

                $document->addLine($line);
            }

            if (!$item->isCompound()) {
                $expected = Invoice\InvoiceTypes::isInvoice($document)
                    ? $this->shipmentCalculator->calculateShippedQuantity($item) // TODO Test
                    : 0;

                // TODO minus shipped quantity (credit case, without linked return ?)
                $available = $this->invoiceCalculator->calculateMaxQuantity($line);

                if (0 < $available) {
                    // Set available and expected quantity
                    $line->setAvailable($available);
                    $line->setExpected($expected);

                    if (Invoice\InvoiceTypes::isInvoice($document) && null === $document->getId()) {
                        // Set default quantity for new non return shipment items
                        $line->setQuantity(min($expected, $available));
                    }
                } else {
                    // Remove unexpected line
                    $document->removeLine($line);
                    $line = null;
                }
            }
        }

        if ($recurse && $item->hasChildren()) {
            if (null !== $line && $item->isCompound()) {
                $available = $expected = null;
                foreach ($item->getChildren() as $childItem) {
                    if (null !== $childLine = $this->buildGoodLine($childItem, $document)) {
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

                    if (Invoice\InvoiceTypes::isInvoice($document) && null === $document->getId()) {
                        // Set default quantity for new non credit invoice lines
                        $line->setQuantity(min($expected, $available));
                    }
                } else {
                    // Remove unexpected line
                    $document->removeLine($line);
                    $item = null;
                }
            } else {
                foreach ($item->getChildren() as $childLine) {
                    $this->buildGoodLine($childLine, $document);
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
        $invoice = $line->getDocument();

        $available = $this->invoiceCalculator->calculateMaxQuantity($line);

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
    protected function createLine(Document\DocumentInterface $document)
    {
        /** @var Invoice\InvoiceInterface $document */
        return $this->saleFactory->createLineForInvoice($document);
    }
}
