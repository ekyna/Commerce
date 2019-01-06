<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Util;

use Ekyna\Component\Commerce\Common\Calculator\Adjustment;
use IntlDateFormatter;
use NumberFormatter;

/**
 * Class Formatter
 * @package Ekyna\Component\Commerce\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Formatter
{
    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var IntlDateFormatter
     */
    private $dateFormatter;

    /**
     * @var IntlDateFormatter
     */
    private $dateTimeFormatter;

    /**
     * @var NumberFormatter
     */
    private $numberFormatter;

    /**
     * @var NumberFormatter
     */
    private $currencyFormatter;


    /**
     * Constructor.
     *
     * @param string $locale
     * @param string $currency
     */
    public function __construct(string $locale = 'FR', string $currency = 'EUR')
    {
        $this->locale = $locale;
        $this->currency = $currency;
    }

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Returns the currency.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Formats the given date for display.
     *
     * @param \DateTime $date
     *
     * @return string
     */
    public function date(\DateTime $date): string
    {
        //$this->dateFormatter->getTimeZone()
        //if ($this->dateFormatter->getTimeZone() === $date->getTimezone();

        return $this->getDateFormatter()->format($date->getTimestamp());
    }

    /**
     * Formats the given date time for display.
     *
     * @param \DateTime $date
     *
     * @return string
     */
    public function dateTime(\DateTime $date): string
    {
        //$this->dateFormatter->getTimeZone()
        //if ($this->dateFormatter->getTimeZone() === $date->getTimezone();

        return $this->getDateTimeFormatter()->format($date->getTimestamp());
    }

    /**
     * Formats the given number for display.
     *
     * @param float $number
     *
     * @return string
     */
    public function number(float $number): string
    {
        return $this->getNumberFormatter()->format($number, NumberFormatter::TYPE_DEFAULT);
    }

    /**
     * Formats the given currency number for display.
     *
     * @param float  $number
     * @param string $currency
     *
     * @return string
     */
    public function currency(float $number, string $currency = null): string
    {
        return $this->getCurrencyFormatter()->formatCurrency($number, $currency ? $currency : $this->currency);
    }

    /**
     * Formats the given percent number for display.
     *
     * @param float $number
     *
     * @return string
     */
    public function percent(float $number): string
    {
        return $this->getNumberFormatter()->format($number, NumberFormatter::TYPE_DEFAULT) . '%';
    }

    /**
     * Formats the given adjustments rates for display.
     *
     * @param Adjustment[] $adjustments
     *
     * @return string
     */
    public function rates(Adjustment ...$adjustments): string
    {
        return implode(', ', array_map(function (Adjustment $adjustment) {
            return $this->percent($adjustment->getRate());
        }, $adjustments));
    }

    /**
     * Returns the date formatter.
     *
     * @return IntlDateFormatter
     */
    private function getDateFormatter()
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
     *
     * @return IntlDateFormatter
     */
    private function getDateTimeFormatter()
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
     *
     * @return NumberFormatter
     */
    private function getNumberFormatter()
    {
        if ($this->numberFormatter) {
            return $this->numberFormatter;
        }

        return $this->numberFormatter = NumberFormatter::create($this->locale, NumberFormatter::DECIMAL);
    }

    /**
     * Returns the currency formatter.
     *
     * @return NumberFormatter
     */
    private function getCurrencyFormatter()
    {
        if ($this->currencyFormatter) {
            return $this->currencyFormatter;
        }

        return $this->currencyFormatter = NumberFormatter::create($this->locale, NumberFormatter::CURRENCY);
    }
}
