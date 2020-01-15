<?php

namespace Ekyna\Component\Commerce\Common\Currency;

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
    /**
     * @var ExchangeRateProviderInterface
     */
    protected $provider;

    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param ExchangeRateProviderInterface $provider
     * @param string                        $currency
     */
    public function __construct(ExchangeRateProviderInterface $provider, string $currency = 'USD')
    {
        $this->provider        = $provider;
        $this->defaultCurrency = strtoupper($currency);
    }

    /**
     * @inheritdoc
     */
    public function convert(
        float $amount,
        string $base,
        string $quote = null,
        \DateTime $date = null,
        bool $round = true
    ): float {
        return $this->convertWithRate($amount, $this->getRate($base, $quote, $date), $quote, $round);
    }

    /**
     * @inheritdoc
     */
    public function convertWithRate(float $amount, float $rate, string $quote = null, bool $round = true): float
    {
        return $round ? Money::round($amount * $rate, $quote ?? $this->defaultCurrency) : $amount * $rate;
    }

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
    ): float {
        $currency = $subject->getCurrency()->getCode();

        $base  = strtoupper($base ?? $this->defaultCurrency);
        $quote = strtoupper($quote ?? $currency);

        if ($base === $quote) {
            return 1.0;
        }

        if ($rate = $subject->getExchangeRate()) {
            if (($base === $this->defaultCurrency) && ($quote === $currency)) {
                return $rate;
            }

            if (($base === $currency) && ($quote === $this->defaultCurrency)) {
                return round(1 / $rate, 5);
            }
        }

        return $this->getRate($base, $quote, $subject->getExchangeDate());
    }

    /**
     * @inheritDoc
     */
    public function convertWithSubject(
        float $amount,
        ExchangeSubjectInterface $subject,
        string $quote = null,
        bool $round = true,
        bool $invert = false // TODO Remove as not used
    ): float {
        if ($invert) {
            trigger_error('Invert parameter should not be used.', E_USER_DEPRECATED);
        }

        if (is_null($quote)) {
            $quote = $subject->getCurrency()->getCode();
        }

        $rate = $this->getSubjectExchangeRate($subject, $this->defaultCurrency, $quote);

        return $this->convertWithRate($amount, $invert ? round(1 / $rate, 5) : $rate, $quote, $round);
    }

    /**
     * @inheritDoc
     */
    public function getRate(string $base, string $quote = null, \DateTime $date = null): float
    {
        $base  = strtoupper($base);
        $quote = strtoupper($quote ?? $this->defaultCurrency);

        if ($base === $quote) {
            return 1.0;
        }

        $date = $date ? clone $date : new \DateTime();
        $date->setTime((int)$date->format('H'), 0, 0, 0);

        if (null !== $rate = $this->provider->get($base, $quote, $date)) {
            return $rate;
        }

        throw new RuntimeException("Failed to retrieve exchange rate.");
    }

    /**
     * @inheritDoc
     */
    public function setSubjectExchangeRate(ExchangeSubjectInterface $subject): bool
    {
        if (!is_null($subject->getExchangeRate())) {
            return false;
        }

        if (null === $currency = $subject->getCurrency()) {
            throw new RuntimeException("Subject currency is not set");
        }

        $date = $subject->getExchangeDate() ?? new \DateTime();

        $rate = $this->getRate($this->defaultCurrency, $currency->getCode(), $date);

        $subject
            ->setExchangeRate($rate)
            ->setExchangeDate($date);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }
}
