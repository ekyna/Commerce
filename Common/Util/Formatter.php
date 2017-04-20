<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Util;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Adjustment;
use IntlDateFormatter;
use NumberFormatter;

/**
 * Class Formatter
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Formatter
{
    private string             $locale;
    private string             $currency;
    private ?IntlDateFormatter $dateFormatter     = null;
    private ?IntlDateFormatter $dateTimeFormatter = null;
    private ?NumberFormatter   $numberFormatter   = null;
    private ?NumberFormatter   $currencyFormatter = null;


    public function __construct(string $locale = 'FR', string $currency = 'EUR')
    {
        $this->locale = $locale;
        $this->currency = $currency;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Formats the given date for display.
     */
    public function date(DateTimeInterface $date): string
    {
        return $this->getDateFormatter()->format($date->getTimestamp());
    }

    /**
     * Formats the given date time for display.
     */
    public function dateTime(DateTimeInterface $date): string
    {
        return $this->getDateTimeFormatter()->format($date->getTimestamp());
    }

    /**
     * Formats the given number for display.
     *
     * @param string|float|int|Decimal $number
     */
    public function number($number, int $scale = null): string
    {
        if ($number instanceof Decimal) {
            $number = $number->toFloat();
        }

        $formatter = $this->getNumberFormatter();

        if ($scale) {
            $formatter = clone $formatter;
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $scale);
        }

        return $formatter->format($number, NumberFormatter::TYPE_DEFAULT);
    }

    /**
     * Formats the given currency number for display.
     *
     * @param string|float|int|Decimal $number
     */
    public function currency($number, string $currency = null): string
    {
        if ($number instanceof Decimal) {
            $number = $number->toFloat();
        }

        return $this->getCurrencyFormatter()->formatCurrency($number, $currency ?? $this->currency);
    }

    /**
     * Formats the given percent number for display.
     *
     * @param string|float|int|Decimal $number
     */
    public function percent($number): string
    {
        if ($number instanceof Decimal) {
            $number = $number->toFloat();
        }

        // TODO getPercentFormatter()

        return $this->getNumberFormatter()->format($number, NumberFormatter::TYPE_DEFAULT) . '%';
    }

    /**
     * Formats the given adjustments rates for display.
     */
    public function rates(Adjustment ...$adjustments): string
    {
        return implode(', ', array_map(function (Adjustment $adjustment) {
            return $this->percent($adjustment->getRate());
        }, $adjustments));
    }

    /**
     * Returns the date formatter.
     */
    private function getDateFormatter(): IntlDateFormatter
    {
        if ($this->dateFormatter) {
            return $this->dateFormatter;
        }

        return $this->dateFormatter = IntlDateFormatter::create(
            $this->locale,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::NONE,
            ini_get('date.timezone'),
            //PHP_VERSION_ID >= 50500 ? $date->getTimezone() : $date->getTimezone()->getName(),
            IntlDateFormatter::GREGORIAN
        );
    }

    /**
     * Returns the date time formatter.
     */
    private function getDateTimeFormatter(): IntlDateFormatter
    {
        if ($this->dateTimeFormatter) {
            return $this->dateTimeFormatter;
        }

        return $this->dateTimeFormatter = IntlDateFormatter::create(
            $this->locale,
            IntlDateFormatter::SHORT,
            IntlDateFormatter::SHORT,
            ini_get('date.timezone'),
            //PHP_VERSION_ID >= 50500 ? $date->getTimezone() : $date->getTimezone()->getName(),
            IntlDateFormatter::GREGORIAN
        );
    }

    /**
     * Returns the number formatter.
     */
    private function getNumberFormatter(): NumberFormatter
    {
        if ($this->numberFormatter) {
            return $this->numberFormatter;
        }

        return $this->numberFormatter = NumberFormatter::create($this->locale, NumberFormatter::DECIMAL);
    }

    /**
     * Returns the currency formatter.
     */
    private function getCurrencyFormatter(): NumberFormatter
    {
        if ($this->currencyFormatter) {
            return $this->currencyFormatter;
        }

        return $this->currencyFormatter = NumberFormatter::create($this->locale, NumberFormatter::CURRENCY);
    }
}
