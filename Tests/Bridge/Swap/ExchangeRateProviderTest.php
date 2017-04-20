<?php

namespace Ekyna\Component\Commerce\Tests\Bridge\Swap;

use Ekyna\Component\Commerce\Bridge\Swap\SwapProvider;
use Ekyna\Component\Commerce\Tests\Fixture;
use Exchanger\Exception\Exception;
use Exchanger\ExchangeRate;
use PHPUnit\Framework\TestCase;
use Swap\Swap;

/**
 * Class ExchangeRateProviderTest
 * @package Ekyna\Component\Commerce\Tests\Bridge\Swap
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExchangeRateProviderTest extends TestCase
{
    public function testGet_historial(): void
    {
        $date = new \DateTime('2020-01-01 12:00:00');

        $swap = $this->createMock(Swap::class);
        $swap->expects($this->once())->method('historical')->with('EUR/USD', $date)->willReturn(new ExchangeRate(1.25));
        $swap->expects($this->never())->method('latest');

        $provider = new SwapProvider($swap, Fixture::CURRENCY_EUR);

        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));
    }

    public function testGet_latest(): void
    {
        $date = new \DateTime();

        $swap = $this->createMock(Swap::class);
        $swap->expects($this->once())->method('latest')->with('EUR/USD')->willReturn(new ExchangeRate(1.25));
        $swap->expects($this->never())->method('historical');

        $provider = new SwapProvider($swap, Fixture::CURRENCY_EUR);

        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));
    }

    public function testGet_invert(): void
    {
        $date = new \DateTime();

        $swap = $this->createMock(Swap::class);
        $swap->expects($this->at(0))->method('latest')->with('USD/EUR')->willThrowException(new Exception());
        $swap->expects($this->at(1))->method('latest')->with('EUR/USD')->willReturn(new ExchangeRate(1.25));
        $swap->expects($this->never())->method('historical');

        $provider = new SwapProvider($swap, Fixture::CURRENCY_EUR);

        $this->assertEquals(0.8, $provider->get(Fixture::CURRENCY_USD, Fixture::CURRENCY_EUR, $date));
    }
}
