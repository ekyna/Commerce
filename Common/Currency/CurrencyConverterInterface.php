<?php

namespace Ekyna\Component\Commerce\Common\Currency;

/**
 * Interface ExchangeRateProviderInterface
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyConverterInterface
{
    /**
     * Converts the given amount regarding to the currencies.
     *
     * @param float          $amount The amount to convert
     * @param string         $base   The base currency ISO 4217 code
     * @param string         $quote  The quote currency ISO 4217 code (if different than default)
     * @param \DateTime|null $date   An optional date for historical rates
     * @param bool           $round  Whether the round the result regarding to currency
     *
     * @return float
     */
    public function convert($amount, $base, $quote = null, \DateTime $date = null, bool $round = true);

    /**
     * Converts the given amount with the given rate.
     *
     * @param float  $amount The amount to convert
     * @param float  $rate   The exchange rate
     * @param string $quote  The quote currency ISO 4217 code (if different than default)
     * @param bool   $round  Whether the round the result regarding to currency
     *
     * @return float
     */
    public function convertWithRate($amount, $rate, $quote = null, bool $round = true);

    /**
     * Returns the exchange rate regarding to the currencies.
     *
     * @param string         $base
     * @param string         $quote
     * @param \DateTime|null $date
     *
     * @return float
     */
    public function getRate($base, $quote = null, \DateTime $date = null);

    /**
     * Returns the default currency.
     *
     * @return string
     */
    public function getDefaultCurrency();
}
