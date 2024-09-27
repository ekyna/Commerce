<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;

/**
 * Interface ExchangeRateProviderInterface
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface CurrencyConverterInterface
{
    /**
     * Converts the given amount regarding the currencies.
     *
     * @param Decimal                $amount The amount to convert
     * @param string                 $base   The base currency ISO 4217 code
     * @param string|null            $quote  The quote currency ISO 4217 code (if different from default)
     * @param DateTimeInterface|null $date   An optional date for historical rates
     * @param bool                   $round  Whether the round the result regarding the currency
     */
    public function convert(
        Decimal           $amount,
        string            $base,
        string            $quote = null,
        DateTimeInterface $date = null,
        bool              $round = true
    ): Decimal;

    /**
     * Converts the given amount with the given rate.
     *
     * @param Decimal     $amount The amount to convert
     * @param Decimal     $rate   The exchange rate
     * @param string|null $quote  The quote currency ISO 4217 code (if different from default)
     * @param bool        $round  Whether the round the result regarding the currency
     */
    public function convertWithRate(Decimal $amount, Decimal $rate, string $quote = null, bool $round = true): Decimal;

    /**
     * Converts the given amount using the subject to guess exchange rate.
     *
     * @param Decimal                  $amount  The amount to convert (default currency)
     * @param ExchangeSubjectInterface $subject The subject
     * @param string|null              $quote   The quote currency ISO 4217 code
     * @param bool                     $round   Whether the round the result regarding the quote currency
     */
    public function convertWithSubject(
        Decimal                  $amount,
        ExchangeSubjectInterface $subject,
        string                   $quote = null,
        bool                     $round = true
    ): Decimal;

    /**
     * Returns the exchange rate base on the given subject's data.
     */
    public function getSubjectExchangeRate(
        ExchangeSubjectInterface $subject,
        string                   $base = null,
        string                   $quote = null
    ): Decimal;

    /**
     * Sets the subject's exchange rate (and date).
     *
     * @return bool Whether the exchange rate has been set.
     */
    public function setSubjectExchangeRate(ExchangeSubjectInterface $subject): bool;

    /**
     * Returns the exchange rate regarding the currencies.
     */
    public function getRate(string $base, string $quote = null, DateTimeInterface $date = null): Decimal;

    /**
     * Returns the default currency.
     *
     * @TODO Rename with 'getSystemCurrency()'
     */
    public function getDefaultCurrency(): string;
}
