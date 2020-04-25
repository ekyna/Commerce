<?php

namespace Ekyna\Component\Commerce\Tests\Bridge\Symfony\Currency;

use Ekyna\Component\Commerce\Bridge\Symfony\Currency\CachedExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * Class CachedExchangeRateProviderTest
 * @package Ekyna\Component\Commerce\Tests\Bridge\Symfony\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CachedExchangeRateProviderTest extends TestCase
{
    public function testGet(): void
    {
        $date = new \DateTime('2020-01-01 12:00:00');

        $cache = $this->createMock(AdapterInterface::class);
        $cache->expects($this->once())->method('hasItem')->with("EUR-USD-202001011200")->willReturn(true);
        $cache->expects($this->once())->method('getItem')->with("EUR-USD-202001011200")->willReturn(
            (new CacheItem())->set(1.25)
        );

        $provider = new CachedExchangeRateProvider($cache);

        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));
    }

    public function testGet_withInversion(): void
    {
        $date = new \DateTime('2020-01-01 12:00:00');

        $cache = $this->createMock(AdapterInterface::class);
        $cache->expects($this->at(0))->method('hasItem')->with("USD-EUR-202001011200")->willReturn(false);

        $cache->expects($this->at(1))->method('hasItem')->with("EUR-USD-202001011200")->willReturn(true);
        $cache->expects($this->once())->method('getItem')->with("EUR-USD-202001011200")->willReturn(
            (new CacheItem())->set(1.25)
        );

        $provider = new CachedExchangeRateProvider($cache);

        $this->assertEquals(0.8, $provider->get(Fixture::CURRENCY_USD, Fixture::CURRENCY_EUR, $date));
    }

    public function testGet_withFallback(): void
    {
        $date = new \DateTime('2020-01-01 12:00:00');

        $cache = $this->createMock(AdapterInterface::class);

        // Exchange rate is not cached for both currency pairs
        $cache->expects($this->at(0))->method('hasItem')->with("EUR-USD-202001011200")->willReturn(false);
        $cache->expects($this->at(1))->method('hasItem')->with("USD-EUR-202001011200")->willReturn(false);

        // Fallback provider will return the exchange rate.
        $fallback = $this->createMock(ExchangeRateProviderInterface::class);
        $fallback
            ->expects($this->once())
            ->method('get')
            ->with(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date)
            ->willReturn(1.25);

        // Provider will save the exchange rate in cache
        $item = new CacheItem();
        $item->set(1.25);
        $cache->expects($this->once())->method('getItem')->with("EUR-USD-202001011200")->willReturn($item);
        $cache->expects($this->once())->method('save')->with($item);

        $provider = new CachedExchangeRateProvider($cache, $fallback);

        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));
    }
}
