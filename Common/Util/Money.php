<?php

namespace Ekyna\Component\Commerce\Common\Util;

use Symfony\Component\Intl\Intl;

/**
 * Class Money
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class Money
{
    /**
     * @var \Symfony\Component\Intl\ResourceBundle\CurrencyBundleInterface
     */
    static private $currencyBundle;

    /**
     * @var array
     */
    static private $precisions = [];

    /**
     * @var array
     */
    static private $increments = [];


    /**
     * Rounds the amount to currency's precision.
     *
     * @param float  $amount
     * @param string $currency
     *
     * @return float
     *
     * @see \Symfony\Component\Intl\NumberFormatter\NumberFormatter::roundCurrency()
     */
    static public function round($amount, $currency)
    {
        $precision = static::getPrecision($currency);
        $roundingIncrement = static::getRoundingIncrement($currency);

        $amount = round($amount, $precision, \PHP_ROUND_HALF_EVEN);

        if (0 < $roundingIncrement && 0 < $precision) {
            $roundingFactor = $roundingIncrement / pow(10, $precision);
            $amount = round($amount / $roundingFactor) * $roundingFactor;
        }

        return $amount;
    }

    /**
     * Compares two amounts regarding to the currency precision.
     *
     * @param float  $a
     * @param float  $b
     * @param string $currency
     *
     * @see bccomp()
     *
     * @return int  1 if $a &gt; $b<br>
     *              0 if $a == $b<br>
     *             -1 if $a &lt; $b
     */
    static public function compare($a, $b, $currency)
    {
        return bccomp($a, $b, static::getPrecision($currency));
    }

    /**
     * Returns the currency precision.
     *
     * @param string $currency
     *
     * @return int|null
     */
    static public function getPrecision($currency)
    {
        if (isset(static::$precisions[$currency])) {
            return static::$precisions[$currency];
        }

        return static::$precisions[$currency] = static::getCurrencyBundle()->getFractionDigits($currency);
    }

    /**
     * Returns the currency rounding increment.
     *
     * @param string $currency
     *
     * @return float|int|null
     */
    static public function getRoundingIncrement($currency)
    {
        if (isset(static::$increments[$currency])) {
            return static::$increments[$currency];
        }

        return static::$increments[$currency] = static::getCurrencyBundle()->getRoundingIncrement($currency);
    }

    /**
     * Returns the currency bundle.
     *
     * @return \Symfony\Component\Intl\ResourceBundle\CurrencyBundleInterface
     */
    static private function getCurrencyBundle()
    {
        if (null !== static::$currencyBundle) {
            return static::$currencyBundle;
        }

        return static::$currencyBundle = Intl::getCurrencyBundle();
    }
}
