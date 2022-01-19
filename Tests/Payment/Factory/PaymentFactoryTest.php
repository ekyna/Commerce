<?php

namespace Ekyna\Component\Commerce\Tests\Payment\Factory;

use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderPayment;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Factory\PaymentFactory;
use Ekyna\Component\Commerce\Payment\Factory\PaymentFactoryInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdater;
use Ekyna\Component\Commerce\Payment\Updater\PaymentUpdaterInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PaymentFactoryTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Factory
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentFactoryTest extends TestCase
{
    /**
     * @var FactoryHelperInterface|MockObject
     */
    private $factoryHelper;

    /**
     * @var PaymentUpdaterInterface
     */
    private $paymentUpdater;

    /**
     * @var PaymentCalculatorInterface|MockObject
     */
    private $paymentCalculator;

    /**
     * @var PaymentFactoryInterface
     */
    private $paymentFactory;


    protected function setUp(): void
    {
        $this->factoryHelper = $this->createMock(FactoryHelperInterface::class);

        $this->paymentUpdater = new PaymentUpdater($this->getCurrencyConverter());

        $this->paymentCalculator = $this->createMock(PaymentCalculatorInterface::class);

        $this->paymentFactory = new PaymentFactory(
            $this->factoryHelper,
            $this->paymentUpdater,
            $this->paymentCalculator,
            $this->getCurrencyConverter(),
            $this->getCurrencyRepositoryMock()
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->paymentFactory = null;
        $this->factoryHelper = null;
        $this->paymentUpdater = null;
        $this->paymentCalculator = null;
    }

    /**
     * @param string $class
     * @param string $subjectCurrency
     * @param bool   $default
     * @param float  $amount
     * @param float  $realAmount
     * @param string $paymentCurrency
     *
     * @dataProvider provide_createPayment
     */
    public function test_createPayment(
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
            ->factoryHelper
            ->method('createPaymentForSale')
            ->with($subject)
            ->willReturn($payment);

        $this
            ->paymentCalculator
            ->method('calculateExpectedPaymentAmount')
            ->with($subject)
            ->willReturn($amount);

        $payment = $this->paymentFactory->createPayment($subject, $method);

        $this->assertEquals($paymentCurrency, $payment->getCurrency()->getCode());
        $this->assertEquals($method, $payment->getMethod());
        $this->assertEquals($amount, $payment->getAmount());
        $this->assertEquals($realAmount, $payment->getRealAmount());
    }

    public function provide_createPayment(): array
    {
        return [
            'Case 1' => [Order::class, Fixture::CURRENCY_EUR, true,  100, 100, Fixture::CURRENCY_EUR],
            'Case 2' => [Order::class, Fixture::CURRENCY_EUR, false, 100, 100, Fixture::CURRENCY_EUR],
            'Case 3' => [Order::class, Fixture::CURRENCY_USD, true,   40,  40, Fixture::CURRENCY_EUR],
            'Case 4' => [Order::class, Fixture::CURRENCY_USD, false,  40,  32, Fixture::CURRENCY_USD],
        ];
    }

    // TODO public function test_createRefund(): void {}
}
