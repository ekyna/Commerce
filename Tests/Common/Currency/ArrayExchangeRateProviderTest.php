<?php

namespace Ekyna\Component\Commerce\Tests\Common\Currency;

use Ekyna\Component\Commerce\Common\Currency\ArrayExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
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

        $this->assertEquals(1.25, $provider->get('EUR', 'USD', new \DateTime()));
        $this->assertEquals(1.25, $provider->get('EUR', 'USD', new \DateTime('-1 month')));
        $this->assertEquals(0.8, $provider->get('USD', 'EUR', new \DateTime()));
        $this->assertEquals(0.8, $provider->get('USD', 'EUR', new \DateTime('-1 month')));
    }

    public function testGet_withFallback(): void
    {
        $date = new \DateTime();

        $fallback = $this->createMock(ExchangeRateProviderInterface::class);
        $fallback->expects($this->once())->method('get')->with('EUR', 'USD', $date)->willReturn(1.25);

        $provider = new ArrayExchangeRateProvider([], $fallback);

        $this->assertEquals(1.25, $provider->get('EUR', 'USD', $date));

        // Subsequent calls won't use fallback provider
        $this->assertEquals(1.25, $provider->get('EUR', 'USD', $date));
        $this->assertEquals(0.8, $provider->get('USD', 'EUR', $date));
    }
}
