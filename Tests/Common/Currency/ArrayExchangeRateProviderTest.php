<?php

namespace Ekyna\Component\Commerce\Tests\Common\Currency;

use Ekyna\Component\Commerce\Common\Currency\ArrayExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use PHPUnit\Framework\TestCase;

/**
 * Class ArrayExchangeRateProviderTest
 * @package Ekyna\Component\Commerce\Tests\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ArrayExchangeRateProviderTest extends TestCase
{
    public function testGet(): void
    {
        $provider = new ArrayExchangeRateProvider([
            'EUR/USD' => 1.25,
        ]);

        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, new \DateTime()));
        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, new \DateTime('-1 month')));
        $this->assertEquals(0.8, $provider->get(Fixture::CURRENCY_USD, Fixture::CURRENCY_EUR, new \DateTime()));
        $this->assertEquals(0.8, $provider->get(Fixture::CURRENCY_USD, Fixture::CURRENCY_EUR, new \DateTime('-1 month')));
    }

    public function testGet_withFallback(): void
    {
        $date = new \DateTime();

        $fallback = $this->createMock(ExchangeRateProviderInterface::class);
        $fallback
            ->expects($this->once())
            ->method('get')
            ->with(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date)
            ->willReturn(1.25);

        $provider = new ArrayExchangeRateProvider([], $fallback);

        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));

        // Subsequent calls won't use fallback provider
        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));
        $this->assertEquals(0.8, $provider->get(Fixture::CURRENCY_USD, Fixture::CURRENCY_EUR, $date));
    }
}
