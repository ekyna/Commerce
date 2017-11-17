<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\View;

use Ekyna\Component\Commerce\Common\Calculator\Adjustment;
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
    public function __construct(string $locale, string $currency)
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
     * Formats the given number for display.
     *
     * @param float $number
     *
     * @return string
     */
    public function number(float $number): string
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
    public function currency(float $number): string
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
    public function percent(float $number): string
    {
        return $this->numberFormatter->format($number, NumberFormatter::TYPE_DEFAULT) . '%';
    }

    /**
     * Formats the given adjustments rates for display.
     *
     * @param Adjustment[] ...$adjustments
     *
     * @return string
     */
    public function rates(Adjustment ...$adjustments): string
    {
        return implode(', ', array_map(function(Adjustment $adjustment) {
            return $this->percent($adjustment->getRate());
        }, $adjustments));
    }
}
