<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilder;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
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
     * Constructor.
     *
     * @param SaleFactoryInterface       $factory
     * @param InvoiceCalculatorInterface $calculator
     * @param PhoneNumberUtil            $phoneNumberUtil
     */
    public function __construct(
        SaleFactoryInterface $factory,
        InvoiceCalculatorInterface $calculator,
        PhoneNumberUtil $phoneNumberUtil = null
    ) {
        parent::__construct($phoneNumberUtil);

        $this->saleFactory = $factory;
        $this->invoiceCalculator = $calculator;
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
     *
     * @param Invoice\InvoiceLineInterface $line
     */
    protected function postBuildLine(Document\DocumentLineInterface $line)
    {
        $invoice = $line->getDocument();

        $max = $this->invoiceCalculator->calculateMaxQuantity($line);

        /** @var Invoice\InvoiceLineInterface $line */
        if (Invoice\InvoiceTypes::isInvoice($invoice)) {
            $line->setQuantity($max);
        } elseif (Invoice\InvoiceTypes::isCredit($invoice)) {
            $line->setQuantity(0);
        }

        if (0 >= $max) {
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
