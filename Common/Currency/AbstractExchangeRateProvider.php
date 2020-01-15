<?php

namespace Ekyna\Component\Commerce\Common\Currency;

/**
 * Class AbstractExchangeRateProvider
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractExchangeRateProvider implements ExchangeRateProviderInterface
{
    /**
     * @var ExchangeRateProviderInterface
     */
    private $fallback;


    /**
     * Constructor.
     *
     * @param ExchangeRateProviderInterface|null $fallback
     */
    public function __construct(ExchangeRateProviderInterface $fallback = null)
    {
        $this->fallback = $fallback;
    }

    /**
     * @inheritDoc
     */
    public function get(string $base, string $quote, \DateTime $date): ?float
    {
        if (null !== $rate = $this->fetch($base, $quote, $date)) {
            return $rate;
        }

        if (null === $this->fallback) {
            return null;
        }

        if (null !== $rate = $this->fallback->get($base, $quote, $date)) {
            $this->persist($base, $quote, $date, $rate);
        }

        return $rate;
    }

    /**
     * Fetches the exchange rate.
     *
     * @param string    $base
     * @param string    $quote
     * @param \DateTime $date
     *
     * @return float|null
     */
    abstract protected function fetch(string $base, string $quote, \DateTime $date): ?float;

    /**
     * Persists the exchange rate.
     *
     * @param string    $base
     * @param string    $quote
     * @param \DateTime $date
     * @param float     $rate
     */
    abstract protected function persist(string $base, string $quote, \DateTime $date, float $rate): void;

    /**
     * Builds the exchange rate cache key.
     *
     * @param string    $base
     * @param string    $quote
     * @param \DateTime $date
     *
     * @return string
     */
    protected function buildKey(string $base, string $quote, \DateTime $date): string
    {
        return sprintf("%s-%s-%s", $base, $quote, $date->format('YmdHi'));
    }
}
