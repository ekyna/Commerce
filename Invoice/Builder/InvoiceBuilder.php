<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilder;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
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
     * @var InvoiceSubjectCalculatorInterface
     */
    private $invoiceCalculator;

    /**
     * @var ShipmentSubjectCalculatorInterface
     */
    private $shipmentCalculator;


    /**
     * Constructor.
     *
     * @param SaleFactoryInterface               $factory
     * @param InvoiceSubjectCalculatorInterface  $invoiceCalculator
     * @param ShipmentSubjectCalculatorInterface $shipmentCalculator
     * @param LocaleProviderInterface            $localeProvider
     * @param PhoneNumberUtil|null               $phoneNumberUtil
     */
    public function __construct(
        SaleFactoryInterface $factory,
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        ShipmentSubjectCalculatorInterface $shipmentCalculator,
        LocaleProviderInterface $localeProvider,
        PhoneNumberUtil $phoneNumberUtil = null
    ) {
        parent::__construct($localeProvider, $phoneNumberUtil);

        $this->saleFactory        = $factory;
        $this->invoiceCalculator  = $invoiceCalculator;
        $this->shipmentCalculator = $shipmentCalculator;
    }

    /**
     * Returns the sale factory.
     *
     * @return SaleFactoryInterface
     */
    public function getSaleFactory(): SaleFactoryInterface
    {
        return $this->saleFactory;
    }

    /**
     * Returns the invoice calculator.
     *
     * @return InvoiceSubjectCalculatorInterface
     */
    public function getInvoiceCalculator(): InvoiceSubjectCalculatorInterface
    {
        return $this->invoiceCalculator;
    }

    /**
     * @inheritdoc
     *
     * @return Invoice\InvoiceLineInterface|null
     */
    public function buildGoodLine(
        Common\SaleItemInterface $item,
        Document\DocumentInterface $document
    ): ?Document\DocumentLineInterface {
        if (!$document instanceof Invoice\InvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceInterface::class);
        }

        // Compound item
        if ($item->isCompound()) {
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
                return $this->findOrCreateGoodLine($document, $item, $available, $expected);
            }

            return null;
        }

        $line = null;

        if ($document->isCredit()) {
            // Credit case
            $available = $this->invoiceCalculator->calculateCreditableQuantity($item, $document);
        } else {
            // Invoice case
            $available = $this->invoiceCalculator->calculateInvoiceableQuantity($item, $document);
        }

        if (0 < $available) {
            $expected = null;
            if (!$document->isCredit()) {
                $expected = max(0, min(
                    $available,
                    $this->shipmentCalculator->calculateShippedQuantity($item)
                    - $this->invoiceCalculator->calculateInvoicedQuantity($item)
                ));
            }

            $line = $this->findOrCreateGoodLine($document, $item, $available, $expected);
        }

        // Build children
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $childLine) {
                $this->buildGoodLine($childLine, $document);
            }
        }

        return $line;
    }

    /**
     * @inheritDoc
     */
    public function buildDiscountLine(
        Common\SaleAdjustmentInterface $adjustment,
        Document\DocumentInterface $document
    ): ?Document\DocumentLineInterface {
        if (!$document instanceof Invoice\InvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceInterface::class);
        }

        $line     = null;
        $expected = null;
        if ($document->isCredit()) {
            // Credit case
            $available = $this->invoiceCalculator->calculateCreditableQuantity($adjustment, $document);
        } else {
            // Invoice case
            $expected = $available = $this->invoiceCalculator->calculateInvoiceableQuantity($adjustment, $document);
        }

        if (0 < $available) {
            /** @var Invoice\InvoiceLineInterface $line */
            $line = parent::buildDiscountLine($adjustment, $document);
            $line
                ->setAvailable($available)
                ->setExpected($expected);

            if (is_null($document->getId())) {
                $line->setQuantity(max(1, $expected));
            }
        }

        return $line;
    }

    /**
     * @inheritDoc
     */
    public function buildShipmentLine(Document\DocumentInterface $document): ?Document\DocumentLineInterface
    {
        if (!$document instanceof Invoice\InvoiceInterface) {
            throw new InvalidArgumentException("Expected instance of " . Invoice\InvoiceInterface::class);
        }

        $line     = null;
        $expected = null;
        $sale     = $document->getSale();
        if ($document->isCredit()) {
            // Credit case
            $available = $this->invoiceCalculator->calculateCreditableQuantity($sale, $document);
        } else {
            // Invoice case
            $expected = $available = $this->invoiceCalculator->calculateInvoiceableQuantity($sale, $document);
        }

        if (0 < $available) {
            /** @var Invoice\InvoiceLineInterface $line */
            $line = parent::buildShipmentLine($document);
            $line
                ->setAvailable($available)
                ->setExpected($expected);

            if (is_null($document->getId())) {
                $line->setQuantity(min(1, $expected));
            }
        }

        return $line;
    }

    /**
     * Finds or create the invoice line.
     *
     * @param Invoice\InvoiceInterface $invoice
     * @param Common\SaleItemInterface $item
     * @param float                    $available
     * @param float|null               $expected
     *
     * @return Invoice\InvoiceLineInterface
     */
    public function findOrCreateGoodLine(
        Invoice\InvoiceInterface $invoice,
        Common\SaleItemInterface $item,
        float $available,
        float $expected = null
    ): ?Invoice\InvoiceLineInterface {
        if (0 >= $available) {
            return null;
        }

        // Create line if not found
        if (null === $line = DocumentUtil::findGoodLine($invoice, $item)) {
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

        if (!$invoice->isCredit() && is_null($invoice->getId())) {
            // Set default quantity for new non return shipment items
            $line->setQuantity(min($expected, $available));
        }

        return $line;
    }

    /**
     * @inheritdoc
     *
     * @return Invoice\InvoiceLineInterface
     */
    protected function createLine(Document\DocumentInterface $document): Document\DocumentLineInterface
    {
        /** @var Invoice\InvoiceInterface $document */
        return $this->saleFactory->createLineForInvoice($document);
    }
}
