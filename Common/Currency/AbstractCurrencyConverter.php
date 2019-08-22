<?php

namespace Ekyna\Component\Commerce\Common\Currency;

use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Exception\RuntimeException;

/**
 * Class AbstractCurrencyConverter
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCurrencyConverter implements CurrencyConverterInterface
{
    /**
     * @var string
     */
    protected $defaultCurrency;


    /**
     * Constructor.
     *
     * @param string $currency
     */
    public function __construct(string $currency = 'USD')
    {
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
        return $round ? Money::round($amount * $rate, $quote) : $amount * $rate;
    }

    /**
     * @inheritDoc
     */
    public function convertWithSubject(
        float $amount,
        ExchangeSubjectInterface $subject,
        string $quote = null,
        bool $round = true
    ): float {
        if (is_null($quote)) {
            $quote = $subject->getCurrency()->getCode();
        }

        $rate = $this->getSubjectExchangeRate($subject, $this->defaultCurrency, $quote);

        return $this->convertWithRate($amount, $rate, $quote, $round);
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
    public function getSubjectExchangeRate(ExchangeSubjectInterface $subject, string $base, string $quote): float
    {
        if ($base === $quote) {
            return 1.0;
        }

        $currency = $subject->getCurrency()->getCode();
        $rate = $subject->getExchangeRate();

        if ($rate) {
            if (($base === $this->defaultCurrency) && ($quote === $currency)) {
                return $rate;
            }

            if (($base === $currency) && ($quote === $this->defaultCurrency)) {
                return 1 / $rate;
            }
        }

        return $this->getRate($base, $quote, $subject->getExchangeDate());
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
