<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\Currency;

use Ekyna\Component\Commerce\Common\Currency\AbstractExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\AdapterInterface;

/**
 * Class CachedExchangeRateProvider
 * @package Ekyna\Component\Commerce\Bridge\Symfony\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CachedExchangeRateProvider extends AbstractExchangeRateProvider
{
    const KEY_PREFIX = 'ecr'; // TODO

    /**
     * @var AdapterInterface
     */
    private $cache;


    /**
     * Constructor.
     *
     * @param AdapterInterface $cache
     * @param ExchangeRateProviderInterface|null $fallback
     */
    public function __construct(AdapterInterface $cache, ExchangeRateProviderInterface $fallback = null)
    {
        parent::__construct($fallback);

        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    protected function fetch(string $base, string $quote, \DateTime $date): ?float
    {
        $key = $this->buildKey($base, $quote, $date);

        if ($this->cache->hasItem($key)) {
            try {
                return $this->cache->getItem($key)->get();
            } catch (InvalidArgumentException $e) {
            }
        }

        $key = $this->buildKey($quote, $base, $date);

        if ($this->cache->hasItem($key)) {
            try {
                return 1 / $this->cache->getItem($key)->get();
            } catch (InvalidArgumentException $e) {
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    protected function persist(string $base, string $quote, \DateTime $date, float $rate): void
    {
        $key = $this->buildKey($base, $quote, $date);

        $item = $this->cache->getItem($key);
        $item->set($rate);

        $this->cache->save($item);
    }
}
