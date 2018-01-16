<?php

namespace Ekyna\Component\Commerce\Common\Converter;

/**
 * Interface ExchangeRateProviderInterface
 * @package Ekyna\Component\Commerce\Common\Converter
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
     *
     * @return float
     */
    public function convert($amount, $base, $quote = null, \DateTime $date = null);

    /**
     * Returns the default currency.
     *
     * @return string
     */
    public function getDefaultCurrency();
}
