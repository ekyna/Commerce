<?php

namespace Ekyna\Component\Commerce\Common\View;

use NumberFormatter;

/**
 * Class Formatter
 * @package Ekyna\Component\Commerce\Common\View
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
    public function __construct($locale, $currency)
    {
        $this->locale = $locale;
        $this->currency = $currency;

        $this->numberFormatter = NumberFormatter::create($locale, NumberFormatter::DECIMAL);
        $this->currencyFormatter = NumberFormatter::create($locale, NumberFormatter::CURRENCY);
    }

    /**
     * Returns the locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Returns the currency.
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Formats the given number for display.
     *
     * @param float $number
     *
     * @return string
     */
    public function number($number)
    {
        return $this->numberFormatter->format($number, NumberFormatter::TYPE_DEFAULT);
    }

    /**
     * Formats the given currency number for display.
     *
     * @param float $number
     *
     * @return string
     */
    public function currency($number)
    {
        return $this->currencyFormatter->formatCurrency($number, $this->currency);
    }

    /**
     * Formats the given percent number for display.
     *
     * @param float $number
     *
     * @return string
     */
    public function percent($number)
    {
        return $this->numberFormatter->format($number, NumberFormatter::TYPE_DEFAULT) . '%';
    }

    /**
     * Formats the given tax rates for display.
     *
     * @param float[] $rates
     *
     * @return string
     */
    public function taxRates(array $rates)
    {
        return implode(', ', array_map(function($rate) {
            return $this->percent($rate);
        }, $rates));
    }
}
