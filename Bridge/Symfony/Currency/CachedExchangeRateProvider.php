<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\Currency;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Currency\AbstractExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class CachedExchangeRateProvider
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CachedExchangeRateProvider extends AbstractExchangeRateProvider
{
    private CacheItemPoolInterface $cache;


    public function __construct(CacheItemPoolInterface $cache, ExchangeRateProviderInterface $fallback = null)
    {
        parent::__construct($fallback);

        $this->cache = $cache;
    }

    protected function fetch(string $base, string $quote, DateTimeInterface $date): ?Decimal
    {
        $key = $this->buildKey($base, $quote, $date);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return new Decimal($item->get());
        }

        $key = $this->buildKey($quote, $base, $date);
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            return (new Decimal(1))->div(new Decimal($item->get()));
        }

        return null;
    }

    protected function persist(string $base, string $quote, DateTimeInterface $date, Decimal $rate): void
    {
        $key = $this->buildKey($base, $quote, $date);

        $item = $this->cache->getItem($key);
        $item->set($rate->toString());

        $this->cache->save($item);
    }
}
