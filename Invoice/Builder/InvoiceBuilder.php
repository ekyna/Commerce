<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model as Common;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilder;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Document\Util\DocumentUtil;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Commerce\Invoice\Resolver\AvailabilityResolverFactory;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use libphonenumber\PhoneNumberUtil;

use function is_null;
use function max;
use function min;

/**
 * Class InvoiceBuilder
 * @package Ekyna\Component\Commerce\Invoice\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceBuilder extends DocumentBuilder implements InvoiceBuilderInterface
{
    private FactoryHelperInterface            $factoryHelper;
    private AvailabilityResolverFactory       $availabilityResolverFactory;
    private InvoiceSubjectCalculatorInterface $invoiceCalculator;

    public function __construct(
        FactoryHelperInterface            $factoryHelper,
        AvailabilityResolverFactory       $availabilityResolverFactory,
        InvoiceSubjectCalculatorInterface $invoiceCalculator,
        LocaleProviderInterface           $localeProvider,
        PhoneNumberUtil                   $phoneNumberUtil = null
    ) {
        parent::__construct($localeProvider, $phoneNumberUtil);

        $this->factoryHelper = $factoryHelper;
        $this->availabilityResolverFactory = $availabilityResolverFactory;
        $this->invoiceCalculator = $invoiceCalculator;
    }

    public function getFactoryHelper(): FactoryHelperInterface
    {
        return $this->factoryHelper;
    }

    public function getInvoiceCalculator(): InvoiceSubjectCalculatorInterface
    {
        return $this->invoiceCalculator;
    }

    /**
     * @return Invoice\InvoiceLineInterface|null
     */
    public function buildGoodLine(
        Common\SaleItemInterface   $item,
        Document\DocumentInterface $document
    ): ?Document\DocumentLineInterface {
        if (!$document instanceof Invoice\InvoiceInterface) {
            throw new UnexpectedTypeException($document, Invoice\InvoiceInterface::class);
        }

        $availability = $this
            ->availabilityResolverFactory
            ->createWithInvoice($document)
            ->resolveSaleItem($item);

        if ($availability->getMaximum()->isZero()) {
            return null;
        }

        $line = $this
            ->findOrCreateGoodLine($document, $item)
            ->setAvailability($availability);

        // Set default quantity for new non-credit invoice lines
        if (!$document->isCredit() && (null === $document->getId())) {
            $line->setQuantity(min(
                $item->getQuantity(),
                $availability->getExpected(),
                $availability->getMaximum()
            ));
        }

        // Build children
        foreach ($item->getChildren() as $childSaleItem) {
            $this->buildGoodLine($childSaleItem, $document);
        }

        return $line;
    }

    public function buildDiscountLine(
        Common\SaleAdjustmentInterface $adjustment,
        Document\DocumentInterface     $document
    ): ?Document\DocumentLineInterface {
        if (!$document instanceof Invoice\InvoiceInterface) {
            throw new UnexpectedTypeException($document, Invoice\InvoiceInterface::class);
        }

        $availability = $this
            ->availabilityResolverFactory
            ->createWithInvoice($document)
            ->resolveSaleDiscount($adjustment);

        if ($availability->getMaximum()->isZero()) {
            return null;
        }

        /** @var Invoice\InvoiceLineInterface $line */
        $line = parent::buildDiscountLine($adjustment, $document);
        $line->setAvailability($availability);

        if (is_null($document->getId())) {
            $line->setQuantity(max(new Decimal(1), $availability->getExpected()));
        }

        return $line;
    }

    public function buildShipmentLine(Document\DocumentInterface $document): ?Document\DocumentLineInterface
    {
        if (!$document instanceof Invoice\InvoiceInterface) {
            throw new UnexpectedTypeException($document, Invoice\InvoiceInterface::class);
        }

        $sale = $document->getSale();

        $availability = $this
            ->availabilityResolverFactory
            ->createWithInvoice($document)
            ->resolveSaleShipment($sale);

        if ($availability->getMaximum()->isZero()) {
            return null;
        }

        /** @var Invoice\InvoiceLineInterface $line */
        $line = parent::buildShipmentLine($document);
        $line->setAvailability($availability);

        if (is_null($document->getId())) {
            $line->setQuantity(min(new Decimal(1), $availability->getExpected()));
        }

        return $line;
    }

    public function findOrCreateGoodLine(
        Invoice\InvoiceInterface $invoice,
        Common\SaleItemInterface $item
    ): ?Invoice\InvoiceLineInterface {
        $line = DocumentUtil::findGoodLine($invoice, $item);

        if ($line instanceof Invoice\InvoiceLineInterface) {
            return $line;
        }

        $line = $this->createLine($invoice);

        return $line
            ->setInvoice($invoice)
            ->setType(Document\DocumentLineTypes::TYPE_GOOD)
            ->setSaleItem($item)
            ->setDesignation($item->getDesignation())
            ->setDescription($item->getDescription())
            ->setReference($item->getReference());
    }

    /**
     * @return Invoice\InvoiceLineInterface
     */
    protected function createLine(Document\DocumentInterface $document): Document\DocumentLineInterface
    {
        /** @var Invoice\InvoiceInterface $document */
        return $this->factoryHelper->createLineForInvoice($document);
    }
}
