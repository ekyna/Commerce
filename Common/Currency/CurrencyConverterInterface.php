<?php

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;

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
     * @param bool           $round  Whether the round the result regarding to the currency
     *
     * @return float
     */
    public function convert(
        float $amount,
        string $base,
        string $quote = null,
        \DateTime $date = null,
        bool $round = true
    ): float;

    /**
     * Converts the given amount with the given rate.
     *
     * @param float  $amount The amount to convert
     * @param float  $rate   The exchange rate
     * @param string $quote  The quote currency ISO 4217 code (if different than default)
     * @param bool   $round  Whether the round the result regarding to the currency
     *
     * @return float
     */
    public function convertWithRate(float $amount, float $rate, string $quote = null, bool $round = true): float;

    /**
     * Converts the given amount using the subject to guess exchange rate.
     *
     * @param float                    $amount  The amount to convert (default currency)
     * @param ExchangeSubjectInterface $subject The subject
     * @param string                   $quote   The quote currency ISO 4217 code
     * @param bool                     $round   Whether the round the result regarding to the currency
     * @param bool                     $invert  Whether to use inverted exchange rate
     *
     * @return float
     */
    public function convertWithSubject(
        float $amount,
        ExchangeSubjectInterface $subject,
        string $quote = null,
        bool $round = true,
        bool $invert = false
    ): float;

    /**
     * Returns the exchange rate base on the given subject's data.
     *
     * @param ExchangeSubjectInterface $subject
     * @param string                   $base
     * @param string                   $quote
     *
     * @return float
     */
    public function getSubjectExchangeRate(
        ExchangeSubjectInterface $subject,
        string $base = null,
        string $quote = null
    ): float;

    /**
     * Sets the subject's exchange rate (and date).
     *
     * @param ExchangeSubjectInterface $subject
     *
     * @return bool Whether the exchange rate has been set.
     */
    public function setSubjectExchangeRate(ExchangeSubjectInterface $subject): bool;

    /**
     * Returns the exchange rate regarding to the currencies.
     *
     * @param string         $base
     * @param string         $quote
     * @param \DateTime|null $date
     *
     * @return float
     */
    public function getRate(string $base, string $quote = null, \DateTime $date = null): float;

    /**
     * Returns the default currency.
     *
     * @return string
     */
    public function getDefaultCurrency(): string;
}
