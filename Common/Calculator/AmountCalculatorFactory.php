<?php

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;

/**
 * Class AmountCalculatorFactory
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AmountCalculatorFactory
{
    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var InvoiceSubjectCalculatorInterface
     */
    private $invoiceCalculator;


    /**
     * Constructor.
     *
     * @param CurrencyConverterInterface        $currencyConverter
     * @param InvoiceSubjectCalculatorInterface $invoiceCalculator
     */
    public function __construct(
        CurrencyConverterInterface $currencyConverter,
        InvoiceSubjectCalculatorInterface $invoiceCalculator
    ) {
        $this->currencyConverter = $currencyConverter;
        $this->invoiceCalculator = $invoiceCalculator;
    }

    /**
     * @param string          $currency The currency
     * @param bool            $revenue  Whether to use revenue mode
     * @param StatFilter|null $filter   The item filter
     *
     * @return AmountCalculatorInterface
     */
    public function create(
        string $currency = null,
        bool $revenue = false,
        StatFilter $filter = null
    ): AmountCalculatorInterface {
        $currency = $currency ?? $this->currencyConverter->getDefaultCurrency();

        $calculator = new AmountCalculator($currency, $revenue, $filter);

        $calculator->setCurrencyConverter($this->currencyConverter);
        $calculator->setInvoiceCalculator($this->invoiceCalculator);
        $calculator->setAmountCalculatorFactory($this);

        return $calculator;
    }
}
