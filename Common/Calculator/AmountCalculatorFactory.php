<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;

/**
 * Class AmountCalculatorFactory
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AmountCalculatorFactory
{
    public function __construct(
        private readonly CurrencyConverterInterface $currencyConverter
    ) {
    }

    /**
     * Creates the amount calculator.
     *
     * @param string|null     $currency The currency
     * @param StatFilter|null $filter   The item filter
     */
    public function create(
        string     $currency = null,
        StatFilter $filter = null
    ): AmountCalculatorInterface {
        $currency = $currency ?: $this->currencyConverter->getDefaultCurrency();

        $calculator = new AmountCalculator($currency, $filter);

        $calculator->setCurrencyConverter($this->currencyConverter);
        $calculator->setAmountCalculatorFactory($this);

        return $calculator;
    }
}
