<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Currency;

use DateTimeInterface;
use Decimal\Decimal;

/**
 * Class AbstractExchangeRateProvider
 * @package Ekyna\Component\Commerce\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractExchangeRateProvider implements ExchangeRateProviderInterface
{
    private ?ExchangeRateProviderInterface $fallback;


    public function __construct(ExchangeRateProviderInterface $fallback = null)
    {
        $this->fallback = $fallback;
    }

    public function get(string $base, string $quote, DateTimeInterface $date): ?Decimal
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
     */
    abstract protected function fetch(string $base, string $quote, DateTimeInterface $date): ?Decimal;

    /**
     * Persists the exchange rate.
     */
    abstract protected function persist(string $base, string $quote, DateTimeInterface $date, Decimal $rate): void;

    /**
     * Builds the exchange rate cache key.
     */
    protected function buildKey(string $base, string $quote, DateTimeInterface $date): string
    {
        return sprintf('ecr-%s-%s-%s', $base, $quote, $date->format('YmdHi'));
    }
}
