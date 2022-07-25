<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Util;

use Decimal\Decimal;
use Symfony\Component\Intl\Currencies;

use function array_key_exists;
use function is_null;

use const PHP_ROUND_HALF_DOWN;

/**
 * Class Money
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class Money
{
    private static array $precisions = [];
    private static array $increments = [];

    /**
     * Rounds the amount to currency's precision.
     *
     * @see \Symfony\Component\Intl\NumberFormatter\NumberFormatter::roundCurrency()
     */
    public static function round(Decimal|string|int|float $amount, string $currency = null): Decimal
    {
        if (is_null($currency)) {
            $precision = 5;
            $roundingIncrement = 0;
        } else {
            $precision = self::getPrecision($currency);
            $roundingIncrement = self::getRoundingIncrement($currency);
        }

        if (!$amount instanceof Decimal) {
            $amount = new Decimal((string)$amount);
        }

        $amount = $amount->round($precision, PHP_ROUND_HALF_DOWN);

        if (0 < $roundingIncrement && 0 < $precision) {
            $roundingFactor = $roundingIncrement / pow(10, $precision);
            $amount = $amount->div($roundingFactor)->round()->mul($roundingFactor);
        }

        return $amount;
    }

    public static function fixed(Decimal $amount, string $currency): string
    {
        return $amount->toFixed(self::getPrecision($currency));
    }

    /**
     * Returns the currency precision.
     *
     * @param string $currency
     *
     * @return int|null
     */
    public static function getPrecision(string $currency): ?int
    {
        if (array_key_exists($currency, self::$precisions)) {
            return self::$precisions[$currency];
        }

        return self::$precisions[$currency] = Currencies::getFractionDigits($currency);
    }

    /**
     * Returns the currency rounding increment.
     */
    public static function getRoundingIncrement(string $currency): int
    {
        if (array_key_exists($currency, self::$increments)) {
            return self::$increments[$currency];
        }

        return self::$increments[$currency] = Currencies::getRoundingIncrement($currency);
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
