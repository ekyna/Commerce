<?php

namespace Ekyna\Component\Commerce\Tests\Payment\Factory;

use Ekyna\Component\Commerce\Common\Currency\ArrayCurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Repository\CurrencyRepositoryInterface;
use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderPayment;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Factory\PaymentFactory;
use Ekyna\Component\Commerce\Payment\Factory\PaymentFactoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdater;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentFactoryTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentFactoryTest extends TestCase
{
    /**
     * @var SaleFactoryInterface|MockObject
     */
    private $saleFactory;

    /**
     * @var PaymentUpdaterInterface
     */
    private $paymentUpdater;

    /**
     * @var PaymentCalculatorInterface|MockObject
     */
    private $paymentCalculator;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var CurrencyRepositoryInterface|MockObject
     */
    private $currencyRepository;

    /**
     * @var PaymentFactoryInterface
     */
    private $paymentFactory;

    protected function setUp(): void
    {
        $this->saleFactory = $this->createMock(SaleFactoryInterface::class);

        $this->currencyConverter = new ArrayCurrencyConverter([
            'EUR/USD' => 1.25,
            'USD/EUR' => 0.80,
        ], 'EUR');

        $this->paymentUpdater = new PaymentUpdater($this->currencyConverter);

        $this->paymentCalculator = $this->createMock(PaymentCalculatorInterface::class);

        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getCode')->willReturn('EUR');

        $this->currencyRepository = $this->createMock(CurrencyRepositoryInterface::class);
        $this->currencyRepository->method('findDefault')->willReturn($currency);

        $this->paymentFactory = new PaymentFactory(
            $this->saleFactory,
            $this->paymentUpdater,
            $this->paymentCalculator,
            $this->currencyConverter,
            $this->currencyRepository
        );
    }

    protected function tearDown(): void
    {
        $this->paymentFactory = null;
        $this->saleFactory = null;
        $this->paymentUpdater = null;
        $this->paymentCalculator = null;
        $this->currencyConverter = null;
        $this->currencyRepository = null;
    }

    /**
     * @param string $class
     * @param string $subjectCurrency
     * @param bool   $default
     * @param float  $amount
     * @param float  $realAmount
     * @param string $paymentCurrency
     *
     * @dataProvider provide_create
     */
    public function test_create(
        string $class,
        string $subjectCurrency,
        bool $default,
        float $amount,
        float $realAmount,
        string $paymentCurrency
    ): void {
        /** @var CurrencyInterface|MockObject $currency */
        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getCode')->willReturn($subjectCurrency);

        /** @var PaymentSubjectInterface $subject */
        $subject = new $class;
        $subject->setCurrency($currency);

        $method = $this->createMock(PaymentMethodInterface::class);
        $method->method('isDefaultCurrency')->willReturn($default);

        $payment = new OrderPayment();

        $this
            ->saleFactory
            ->method('createPaymentForSale')
            ->with($subject)
            ->willReturn($payment);

        $this
            ->paymentCalculator
            ->method('calculateRemainingTotal')
            ->with($subject)
            ->willReturn($amount);

        $payment = $this->paymentFactory->createPayment($subject, $method);

        $this->assertEquals($paymentCurrency, $payment->getCurrency()->getCode());
        $this->assertEquals($method, $payment->getMethod());
        $this->assertEquals($amount, $payment->getAmount());
        $this->assertEquals($realAmount, $payment->getRealAmount());
    }

    public function provide_create(): array
    {
        return [
            'Case 1' => [Order::class, 'EUR', true,  100, 100, 'EUR'],
            'Case 2' => [Order::class, 'EUR', false, 100, 100, 'EUR'],
            'Case 3' => [Order::class, 'USD', true,   40,  40, 'EUR'],
            'Case 4' => [Order::class, 'USD', false,  40,  32, 'USD'],
        ];
    }
}
