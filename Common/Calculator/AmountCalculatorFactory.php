<?php

declare(strict_types=1);

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
    public function __construct(
        private readonly CurrencyConverterInterface        $currencyConverter,
        private readonly InvoiceSubjectCalculatorInterface $invoiceCalculator
    ) {
    }

    /**
     * Creates the amount calculator.
     *
     * @param string|null     $currency The currency
     * @param bool            $profit   Whether to use revenue mode
     * @param StatFilter|null $filter   The item filter
     */
    public function create(
        string     $currency = null,
        bool       $profit = false,
        StatFilter $filter = null
    ): AmountCalculatorInterface {
        $currency = $currency ?: $this->currencyConverter->getDefaultCurrency();

        $calculator = new AmountCalculator($currency, $profit, $filter);

        $calculator->setCurrencyConverter($this->currencyConverter);
        $calculator->setInvoiceCalculator($this->invoiceCalculator);
        $calculator->setAmountCalculatorFactory($this);

        return $calculator;
    }
}
