<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class CurrencyConverter
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyConverter implements CurrencyConverterInterface
{
    protected ExchangeRateProviderInterface $provider;
    protected string                        $defaultCurrency;


    public function __construct(ExchangeRateProviderInterface $provider, string $currency = 'USD')
    {
        $this->provider = $provider;
        $this->defaultCurrency = strtoupper($currency);
    }

    public function convert(
        Decimal            $amount,
        string             $base,
        string             $quote = null,
        ?DateTimeInterface $date = null,
        bool               $round = true
    ): Decimal {
        return $this->convertWithRate($amount, $this->getRate($base, $quote, $date), $quote, $round);
    }

    public function convertWithRate(Decimal $amount, Decimal $rate, string $quote = null, bool $round = true): Decimal
    {
        $amount = $amount->mul($rate);

        if ($round) {
            return Money::round($amount, $quote ?? $this->defaultCurrency);
        }

        return $amount;
    }

    public function getSubjectExchangeRate(
        ExchangeSubjectInterface $subject,
        string                   $base = null,
        string                   $quote = null
    ): Decimal {
        $default = $this->defaultCurrency;
        $currency = $subject->getCurrency()->getCode();

        $base = strtoupper($base ?? $subject->getBaseCurrency() ?? $default);
        $quote = strtoupper($quote ?? $currency);

        if ($base === $quote) {
            return new Decimal(1);
        }

        if ($rate = $subject->getExchangeRate()) {
            if (($base === $default) && ($quote === $currency)) {
                return $rate;
            }

            if (($base === $currency) && ($quote === $default)) {
                return (new Decimal(1))->div($rate)->round(5);
            }
        }

        return $this->getRate($base, $quote, $subject->getExchangeDate());
    }

    public function convertWithSubject(
        Decimal                  $amount,
        ExchangeSubjectInterface $subject,
        string                   $quote = null,
        bool                     $round = true
    ): Decimal {
        if (is_null($quote)) {
            $quote = $subject->getCurrency()->getCode();
        }

        $rate = $this->getSubjectExchangeRate($subject, null, $quote);

        return $this->convertWithRate($amount, $rate, $quote, $round);
    }

    public function getRate(string $base, string $quote = null, DateTimeInterface $date = null): Decimal
    {
        $base = strtoupper($base);
        $quote = strtoupper($quote ?? $this->defaultCurrency);

        if ($base === $quote) {
            return new Decimal(1);
        }

        $date = $date ? clone $date : new DateTime();
        $date->setTime((int)$date->format('H'), 0);

        if ($rate = $this->provider->get($base, $quote, $date)) {
            return $rate;
        }

        throw new RuntimeException('Failed to retrieve exchange rate.');
    }

    public function setSubjectExchangeRate(ExchangeSubjectInterface $subject): bool
    {
        if (!is_null($subject->getExchangeRate())) {
            return false;
        }

        if (null === $currency = $subject->getCurrency()) {
            throw new RuntimeException('Subject currency is not set');
        }

        $date = $subject->getExchangeDate() ?? new DateTime();

        $rate = $this->getRate($this->defaultCurrency, $currency->getCode(), $date);

        $subject
            ->setExchangeRate($rate)
            ->setExchangeDate($date);

        return true;
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }
}
