<?php

namespace Ekyna\Component\Commerce\Tests\Bridge\Doctrine\ORM\Provider;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Statement;
use Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Provider\ExchangeRateProvider;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use PHPUnit\Framework\TestCase;

/**
 * Class ExchangeRateProviderTest
 * @package Ekyna\Component\Commerce\Tests\Bridge\Doctrine\ORM\Provider
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ExchangeRateProviderTest extends TestCase
{
    public function testGet_notFound(): void
    {
        $date = new \DateTime('2020-01-01 12:00:00');

        $find = $this->createMock(Statement::class);

        $find
            ->expects($this->at(0))
            ->method('execute')
            ->with([
                'base'  => Fixture::CURRENCY_EUR,
                'quote' => Fixture::CURRENCY_USD,
                'date'  => '2020-01-01 12:00:00',
            ])
            ->willReturn(true);

        $find
            ->expects($this->at(1))
            ->method('fetchColumn')
            ->with(0)
            ->willReturn(false);

        $find
            ->expects($this->at(2))
            ->method('execute')
            ->with([
                'base'  => Fixture::CURRENCY_USD,
                'quote' => Fixture::CURRENCY_EUR,
                'date'  => '2020-01-01 12:00:00',
            ])
            ->willReturn(true);

        $find
            ->expects($this->at(3))
            ->method('fetchColumn')
            ->with(0)
            ->willReturn(false);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('prepare')
            ->withAnyParameters()
            ->willReturn($find);

        $provider = new ExchangeRateProvider($connection);

        $this->assertEquals(null, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));
    }

    public function testGet_found(): void
    {
        $date = new \DateTime('2020-01-01 12:00:00');

        $find = $this->createMock(Statement::class);

        $find
            ->expects($this->once())
            ->method('execute')
            ->with([
                'base'  => Fixture::CURRENCY_EUR,
                'quote' => Fixture::CURRENCY_USD,
                'date'  => '2020-01-01 12:00:00',
            ])
            ->willReturn(true);

        $find
            ->expects($this->once())
            ->method('fetchColumn')
            ->with(0)
            ->willReturn(1.25);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('prepare')
            ->withAnyParameters()
            ->willReturn($find);

        $provider = new ExchangeRateProvider($connection);

        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));
    }

    public function testGet_foundInvert(): void
    {
        $date = new \DateTime('2020-01-01 12:00:00');

        $find = $this->createMock(Statement::class);

        $find
            ->expects($this->at(0))
            ->method('execute')
            ->with([
                'base'  => Fixture::CURRENCY_USD,
                'quote' => Fixture::CURRENCY_EUR,
                'date'  => '2020-01-01 12:00:00',
            ])
            ->willReturn(true);

        $find
            ->expects($this->at(1))
            ->method('fetchColumn')
            ->with(0)
            ->willReturn(false);

        $find
            ->expects($this->at(2))
            ->method('execute')
            ->with([
                'base'  => Fixture::CURRENCY_EUR,
                'quote' => Fixture::CURRENCY_USD,
                'date'  => '2020-01-01 12:00:00',
            ])
            ->willReturn(true);

        $find
            ->expects($this->at(3))
            ->method('fetchColumn')
            ->with(0)
            ->willReturn(1.25);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('prepare')
            ->withAnyParameters()
            ->willReturn($find);

        $provider = new ExchangeRateProvider($connection);

        $this->assertEquals(0.8, $provider->get(Fixture::CURRENCY_USD, Fixture::CURRENCY_EUR, $date));
    }

    public function testGet_withFallback(): void
    {
        $date = new \DateTime('2020-01-01 12:00:00');

        $find = $this->createMock(Statement::class);

        $find
            ->expects($this->at(0))
            ->method('execute')
            ->with([
                'base'  => Fixture::CURRENCY_EUR,
                'quote' => Fixture::CURRENCY_USD,
                'date'  => '2020-01-01 12:00:00',
            ])
            ->willReturn(true);

        $find
            ->expects($this->at(1))
            ->method('fetchColumn')
            ->with(0)
            ->willReturn(false);

        $find
            ->expects($this->at(2))
            ->method('execute')
            ->with([
                'base'  => Fixture::CURRENCY_USD,
                'quote' => Fixture::CURRENCY_EUR,
                'date'  => '2020-01-01 12:00:00',
            ])
            ->willReturn(true);

        $find
            ->expects($this->at(3))
            ->method('fetchColumn')
            ->with(0)
            ->willReturn(false);

        $create = $this->createMock(Statement::class);

        $create
            ->expects($this->once())
            ->method('execute')
            ->with([
                'base'  => Fixture::CURRENCY_EUR,
                'quote' => Fixture::CURRENCY_USD,
                'date'  => '2020-01-01 12:00:00',
                'rate'  => 1.25,
            ])
            ->willReturn(true);

        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->at(0))
            ->method('prepare')
            ->withAnyParameters()
            ->willReturn($find);

        $connection
            ->expects($this->at(1))
            ->method('prepare')
            ->withAnyParameters()
            ->willReturn($create);

        $fallback = $this->createMock(ExchangeRateProviderInterface::class);
        $fallback
            ->expects($this->once())
            ->method('get')
            ->with(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date)
            ->willReturn(1.25);

        $provider = new ExchangeRateProvider($connection, $fallback);

        $this->assertEquals(1.25, $provider->get(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD, $date));
    }
}
