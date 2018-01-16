<?php

namespace Ekyna\Component\Commerce\Common\Converter;

use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;

/**
 * Class ArrayCurrencyConverter
 * @package Ekyna\Component\Commerce\Common\Converter
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ArrayCurrencyConverter implements CurrencyConverterInterface
{
    /**
     * @var array
     */
    private $rates;

    /**
     * @var string
     */
    private $defaultCurrency;


    /**
     * Constructor.
     *
     * @param array  $rates
     * @param string $defaultCurrency
     */
    public function __construct(array $rates, $defaultCurrency = 'USD')
    {
        $this->rates = [];
        $this->defaultCurrency = $defaultCurrency;

        foreach ($rates as $pair => $rate) {
            $this->addRate($pair, $rate);
        }
    }

    /**
     * Adds the conversion rate.
     *
     * @param string $pair
     * @param float  $rate
     *
     * @return ArrayCurrencyConverter
     */
    private function addRate($pair, $rate)
    {
        if (!preg_match('~^[A-Z]{3}/[A-Z]{3}$~', $pair)) {
            throw new InvalidArgumentException("Unexpected currency pair '$pair'.");
        }

        if (!(is_float($rate) && 0 < $rate)) {
            throw new InvalidArgumentException("Unexpected rate '$rate'.");
        }

        $this->rates[$pair] = $rate;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function convert($amount, $base, $quote = null, \DateTime $date = null)
    {
        $quote = $quote ?: $this->defaultCurrency;

        if ($base === $quote) {
            $rate = 1.0;
        } else {
            $pair = "$base/$quote";
            if (!isset($this->rates[$pair])) {
                throw new InvalidArgumentException("Undefined conversion pair '$pair'.");
            }
            $rate = $this->rates[$pair];
        }

        return Money::round($amount / $rate, $quote);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCurrency()
    {
        return $this->defaultCurrency;
    }
}
