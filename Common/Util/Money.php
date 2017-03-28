<?php

namespace Ekyna\Component\Commerce\Common\Util;

use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Payum\ISO4217\ISO4217;

/**
 * Class Money
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Money
{
    /**
     * Rounds the amount to currency's precision.
     *
     * @param float                 $amount
     * @param CurrencyInterface|int $precision
     *
     * @return float
     */
    static function round($amount, $precision)
    {
        return round($amount, static::currencyPrecision($precision));
    }

    /**
     * Compares two amounts regarding to the currency precision.
     *
     * @param float                 $a
     * @param float                 $b
     * @param CurrencyInterface|int $precision
     *
     * @see bccomp()
     *
     * @return int -1 if $a &gt; $b<br>
     *              0 if $a == $b<br>
     *              1 if $a &lt; $b
     */
    static function compare($a, $b, $precision)
    {
        $precision = static::currencyPrecision($precision);

        return bccomp($a, $b, $precision);
    }

    /**
     * @var ISO4217
     */
    static private $iso;

    /**
     * @return ISO4217
     */
    static private function getIso()
    {
        if (null !== static::$iso) {
            return static::$iso;
        }

        return static::$iso = new ISO4217();
    }

    /**
     * Returns the currency precision.
     *
     * @param CurrencyInterface|int $currencyOrPrecision
     *
     * @return int
     */
    static private function currencyPrecision($currencyOrPrecision)
    {
        // TODO cache precision or store it in the currencies

        if ($currencyOrPrecision instanceof CurrencyInterface) {
            /** @var \Payum\ISO4217\Currency $currency */
            $currency = static::getIso()->findByCode($currencyOrPrecision->getCode());

            return $currency->getExp();
        }

        return intval($currencyOrPrecision);
    }
}
