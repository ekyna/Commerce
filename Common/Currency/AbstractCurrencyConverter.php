<?php

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Common\Util\Money;

/**
 * Class AbstractCurrencyConverter
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCurrencyConverter implements CurrencyConverterInterface
{
    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $defaultCurrency
     */
    public function __construct($defaultCurrency = 'USD')
    {
        $this->defaultCurrency = strtoupper($defaultCurrency);
    }

    /**
     * @inheritdoc
     */
    public function convert($amount, $base, $quote = null, \DateTime $date = null, bool $round = true)
    {
        return $this->convertWithRate($amount, $this->getRate($base, $quote, $date), $quote, $round);
    }

    /**
     * @inheritdoc
     */
    public function convertWithRate($amount, $rate, $quote = null, bool $round = true)
    {
        return $round ? Money::round($amount * $rate, $quote) : $amount * $rate;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }
}
