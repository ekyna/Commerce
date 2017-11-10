<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilder;
use Ekyna\Component\Commerce\Document\Model as Document;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use libphonenumber\PhoneNumberUtil;

/**
 * Class InvoiceBuilder
 * @package Ekyna\Component\Commerce\Invoice\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceBuilder extends DocumentBuilder
{
    /**
     * @var SaleFactoryInterface
     */
    private $factory;

    /**
     * @var InvoiceCalculatorInterface
     */
    private $calculator;


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

        $this->factory = $factory;
        $this->calculator = $calculator;
    }

    /**
     * @inheritdoc
     */
    protected function postBuildLine(Document\DocumentLineInterface $line)
    {
        $invoice = $line->getDocument();

        /** @var Invoice\InvoiceLineInterface $line */
        if ($invoice->getType() === Invoice\InvoiceTypes::TYPE_INVOICE) {
            $max = $this->calculator->calculateInvoiceableQuantity($line);
            $line->setQuantity($max);
        } elseif ($invoice->getType() === Invoice\InvoiceTypes::TYPE_CREDIT) {
            $max = $this->calculator->calculateCreditableQuantity($line);
            $line->setQuantity(0);
        } else {
            throw new InvalidArgumentException("Unexpected invoice type.");
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
        return $this->factory->createLineForInvoice($document);
    }
}
