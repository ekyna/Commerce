<?php

namespace Ekyna\Component\Commerce\Tests\Common\Currency;

use Ekyna\Component\Commerce\Common\Currency\CurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\ExchangeRateProviderInterface;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectInterface;
use Ekyna\Component\Commerce\Common\Model\ExchangeSubjectTrait;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Tests\Fixture;
use PHPUnit\Framework\TestCase;

/**
 * Class CurrencyConverterTest
 * @package Ekyna\Component\Commerce\Tests\Common\Currency
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CurrencyConverterTest extends TestCase
{
    /**
     * @var ExchangeRateProviderInterface
     */
    private $provider;

    /**
     * @var CurrencyConverter
     */
    private $converter;


    protected function setUp(): void
    {
        $this->provider = $this->createMock(ExchangeRateProviderInterface::class);
        $this
            ->provider
            ->expects($this->any())
            ->method('get')
            ->will($this->returnCallback([$this, 'fakeRate']));

        $this->converter = new CurrencyConverter($this->provider, Fixture::CURRENCY_EUR);
    }

    public function fakeRate(string $base, string $quote, \DateTime $date = null): float
    {
        if ($base === $quote) {
            return 1.0;
        }

        $alt = $date && ('20200101' === $date->format('Ymd'));

        switch ("$base/$quote") {
            case "EUR/USD":
                return $alt ? 1.13579 : 1.12346;
            case "USD/EUR":
                return $alt ? 0.88044 : 0.89011;
            case "EUR/JPY":
                return $alt ? 122.43219 : 121.98765;
            case "JPY/EUR":
                return $alt ? 0.00816 : 0.00819;
        }

        throw new \RuntimeException("Unexpected currency pair.");
    }

    protected function tearDown(): void
    {
        $this->provider  = null;
        $this->converter = null;
    }

    /**
     * @param float $expected
     * @param array $args
     *
     * @dataProvider provideTestConvertWithRate
     */
    public function testConvertWithRate(float $expected, array $args): void
    {
        $actual = $this->converter->convertWithRate(...$args);

        $this->assertEquals($expected, $actual);
    }

    public function provideTestConvertWithRate(): \Generator
    {
        yield [12, [10, 1.2]];
        yield [-12, [-10, 1.2, Fixture::CURRENCY_EUR]];
        yield [112, [100, 1.123456789, 'JPY']];
        yield [121.9326, [98.76543, 1.2345678, 'CLF']];
        yield [88.75255, [79, 1.12345, null, false]];
    }

    /**
     * @param float $expected
     * @param array $args
     *
     * @dataProvider provideTestConvert
     */
    public function testConvert(float $expected, array $args): void
    {
        $actual = $this->converter->convert(...$args);

        $this->assertEquals($expected, $actual);
    }

    public function provideTestConvert(): \Generator
    {
        yield [12.34, [12.34, Fixture::CURRENCY_EUR]];
        yield [13.87, [12.3456, Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD]];
        yield [1506, [12.3456, Fixture::CURRENCY_EUR, 'JPY']];
    }

    public function testGetRate(): void
    {
        $this->assertEquals(1.12346, $this->converter->getRate(Fixture::CURRENCY_EUR, Fixture::CURRENCY_USD));
        $this->assertEquals(0.89011, $this->converter->getRate(Fixture::CURRENCY_USD, Fixture::CURRENCY_EUR));
        $this->assertEquals(121.98765, $this->converter->getRate(Fixture::CURRENCY_EUR, 'JPY'));
        $this->assertEquals(0.00819, $this->converter->getRate('JPY', Fixture::CURRENCY_EUR));
    }

    /**
     * @param float $expected
     * @param array $args
     *
     * @dataProvider provideTestGetSubjectExchangeRate
     */
    public function testGetSubjectExchangeRate(float $expected, array $args): void
    {
        $actual = $this->converter->getSubjectExchangeRate(...$args);

        $this->assertEquals($expected, $actual);
    }

    public function provideTestGetSubjectExchangeRate(): \Generator
    {
        // Quote EUR
        yield [1.0, [$this->createSubject(Fixture::CURRENCY_EUR)]];
        // Quote EUR, ignore subject exchange rate
        yield [1.0, [$this->createSubject(Fixture::CURRENCY_EUR, 0.12346)]];
        // Subject does not have exchange rate, use exchange rate provider
        yield [1.12346, [$this->createSubject(Fixture::CURRENCY_USD)]];
        // Quote EUR
        yield [1.0, [$this->createSubject(Fixture::CURRENCY_USD), null, Fixture::CURRENCY_EUR]];
        // Quote USD , use subject exchange rate
        yield [1.13468, [$this->createSubject(Fixture::CURRENCY_USD, 1.13468)]];
        // Quote EUR, use inverted subject exchange rate
        yield [0.88131, [$this->createSubject(Fixture::CURRENCY_USD, 1.13468), Fixture::CURRENCY_USD, Fixture::CURRENCY_EUR]];
        // Quote USD, use exchange rate provider with date
        yield [1.13579, [$this->createSubject(Fixture::CURRENCY_USD, null, new \DateTime('2020-01-01'))]];
        // Quote JPY, ignore subject exchange rate, use exchange rate provider with date
        yield [122.43219, [$this->createSubject(Fixture::CURRENCY_USD, 1.13468, new \DateTime('2020-01-01')), null, 'JPY']];
    }

    /**
     * @param float $expected
     * @param array $args
     *
     * @dataProvider provideTestConvertWithSubject
     */
    public function testConvertWithSubject(float $expected, array $args): void
    {
        $actual = $this->converter->convertWithSubject(...$args);

        $this->assertEquals($expected, $actual);
    }

    public function provideTestConvertWithSubject(): \Generator
    {
        // Quote EUR
        yield [123.45, [123.45, $this->createSubject(Fixture::CURRENCY_EUR)]];
        // Quote USD
        yield [138.69, [123.45, $this->createSubject(Fixture::CURRENCY_USD)]];
        // Quote USD, dot round
        yield [138.691137, [123.45, $this->createSubject(Fixture::CURRENCY_USD), null, false]];
        // Quote EUR
        yield [123.45, [123.45, $this->createSubject(Fixture::CURRENCY_USD), Fixture::CURRENCY_EUR]];
        // Quote USD, use subject exchange rate
        yield [154.46, [123.45, $this->createSubject(Fixture::CURRENCY_USD, 1.25123)]];
        // Quote USD, Use exchange rate provider with date
        yield [140.21, [123.45, $this->createSubject(Fixture::CURRENCY_USD, null, new \DateTime('2020-01-01'))]];
    }

    public function testSetSubjectExchangeRate_withoutCurrency(): void
    {
        $subject = $this->createSubject();
        $this->expectException(RuntimeException::class);
        $this->converter->setSubjectExchangeRate($subject);
    }

    /**
     * @param bool                     $changed
     * @param float                    $rate
     * @param ExchangeSubjectInterface $subject
     *
     * @dataProvider provideTestSetSubjectExchangeRate
     */
    public function testSetSubjectExchangeRate(bool $changed, float $rate, ExchangeSubjectInterface $subject): void
    {
        $this->assertEquals($changed, $this->converter->setSubjectExchangeRate($subject));
        $this->assertEquals($rate, $subject->getExchangeRate());
    }

    public function provideTestSetSubjectExchangeRate(): \Generator
    {
        // Default currency -> change to 1.0
        yield [true, 1.0, $this->createSubject(Fixture::CURRENCY_EUR)];
        // USD -> changed to exchange provider's current rate
        yield [true, 1.12346, $this->createSubject(Fixture::CURRENCY_USD)];
        // USD -> changed to exchange provider's rate using date
        yield [true, 1.13579, $this->createSubject(Fixture::CURRENCY_USD, null, new \DateTime('2020-01-01'))];
        // USD -> not changed to exchange provider's rate using date
        yield [false, 1.13579, $this->createSubject(Fixture::CURRENCY_USD, 1.13579)];
    }

    private function createSubject(string $currency = null, float $rate = null, \DateTime $date = null): ExchangeSubjectInterface
    {
        $subject = new class implements ExchangeSubjectInterface { use ExchangeSubjectTrait; };

        if ($currency) {
            $subject->setCurrency(Fixture::currency($currency));
        }

        return $subject
            ->setExchangeRate($rate)
            ->setExchangeDate($date);
    }
}
