<?php

namespace Ekyna\Component\Commerce\Tests\Payment\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Currency\ArrayCurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\Amount;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderPayment;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculator;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentSubjectInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentCalculatorTest extends TestCase
{
    const DEFAULT     = 0;
    const MANUAL      = 1;
    const OUTSTANDING = 2;

    /**
     * @var AmountCalculatorInterface|MockObject
     */
    private $amountCalculator;

    /**
     * @var CurrencyConverterInterface
     */
    private $currencyConverter;

    /**
     * @var PaymentCalculator
     */
    private $paymentCalculator;

    /**
     * @var CurrencyInterface[]|MockObject[]
     */
    private $currencies = [];

    /**
     * @var PaymentMethodInterface[]|MockObject[]
     */
    private $methods = [];


    protected function setUp(): void
    {
        $this->currencyConverter = new ArrayCurrencyConverter([
            'EUR/USD' => 1.25,
            'USD/EUR' => 0.80,
        ], 'EUR');

        $this->amountCalculator = $this->createMock(AmountCalculatorInterface::class);
        $this->paymentCalculator = new PaymentCalculator($this->amountCalculator, $this->currencyConverter);
    }

    protected function tearDown(): void
    {
        $this->currencyConverter = null;
        $this->amountCalculator = null;
        $this->paymentCalculator = null;
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param array                   $results
     *
     * @dataProvider provide_calculatePaidTotal
     */
    public function test_calculatePaidTotal(PaymentSubjectInterface $subject, array $results): void
    {
        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculatePaidTotal($subject, $currency));
        }
    }

    public function provide_calculatePaidTotal(): \Generator
    {
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_FAILED);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 100);
        $this->createPayment($subject, 'EUR', 100, 1);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 100);
        $this->createPayment($subject, 'USD', 125, 1.25);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 100);
        $this->createPayment($subject, 'USD', 100, 1.25);
        $this->createPayment($subject, 'EUR', 20, 1);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param array                   $results
     *
     * @dataProvider provide_calculateOutstandingAcceptedTotal
     */
    public function test_calculateOutstandingAcceptedTotal(PaymentSubjectInterface $subject, array $results): void
    {
        foreach ($results as $currency => $expected) {
            $actual = $this->paymentCalculator->calculateOutstandingAcceptedTotal($subject, $currency);
            $this->assertEquals($expected, $actual);
        }
    }

    public function provide_calculateOutstandingAcceptedTotal(): \Generator
    {
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_FAILED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 125, 1.25, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 100, 1.25, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        $this->createPayment($subject, 'EUR', 20, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param array                   $results
     *
     * @dataProvider provide_calculateOutstandingExpiredTotal
     */
    public function test_calculateOutstandingExpiredTotal(PaymentSubjectInterface $subject, array $results): void
    {
        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected,
                $this->paymentCalculator->calculateOutstandingExpiredTotal($subject, $currency));
        }
    }

    public function provide_calculateOutstandingExpiredTotal(): \Generator
    {
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_EXPIRED);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_EXPIRED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 125, 1.25, PaymentStates::STATE_EXPIRED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 100, 1.25, PaymentStates::STATE_EXPIRED, self::OUTSTANDING);
        $this->createPayment($subject, 'EUR', 20, 1, PaymentStates::STATE_EXPIRED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param array                   $results
     *
     * @dataProvider provide_calculateRefundedTotal
     */
    public function test_calculateRefundedTotal(PaymentSubjectInterface $subject, array $results): void
    {
        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculateRefundedTotal($subject, $currency));
        }
    }

    public function provide_calculateRefundedTotal(): \Generator
    {
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_REFUNDED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_REFUNDED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 125, 1.25, PaymentStates::STATE_REFUNDED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 100, 1.25, PaymentStates::STATE_REFUNDED);
        $this->createPayment($subject, 'EUR', 20, 1, PaymentStates::STATE_REFUNDED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param array                   $results
     *
     * @dataProvider provide_calculateFailedTotal
     */
    public function test_calculateFailedTotal(PaymentSubjectInterface $subject, array $results): void
    {
        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculateFailedTotal($subject, $currency));
        }
    }

    public function provide_calculateFailedTotal(): \Generator
    {
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_FAILED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_FAILED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 125, 1.25, PaymentStates::STATE_FAILED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 100, 1.25, PaymentStates::STATE_FAILED);
        $this->createPayment($subject, 'EUR', 20, 1, PaymentStates::STATE_FAILED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param array                   $results
     *
     * @dataProvider provide_calculateCanceledTotal
     */
    public function test_calculateCanceledTotal(PaymentSubjectInterface $subject, array $results): void
    {
        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculateCanceledTotal($subject, $currency));
        }
    }

    public function provide_calculateCanceledTotal(): \Generator
    {
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CANCELED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CANCELED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 125, 1.25, PaymentStates::STATE_CANCELED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 100, 1.25, PaymentStates::STATE_CANCELED);
        $this->createPayment($subject, 'EUR', 20, 1, PaymentStates::STATE_CANCELED);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param array                   $results
     *
     * @dataProvider provide_calculateOfflinePendingTotal
     */
    public function test_calculateOfflinePendingTotal(PaymentSubjectInterface $subject, array $results): void
    {
        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculateOfflinePendingTotal($subject, $currency));
        }
    }

    public function provide_calculateOfflinePendingTotal(): \Generator
    {
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED, self::MANUAL);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_PENDING);
        yield [$subject, ['EUR' => 0, 'USD' => 0]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 125, 1.25, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 100, 1.25, PaymentStates::STATE_PENDING, self::MANUAL);
        $this->createPayment($subject, 'EUR', 20, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 100, 'USD' => 125]];
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param array                   $results
     * @param array                   $amounts
     *
     * @dataProvider provide_calculateRemainingTotal
     */
    public function test_calculateRemainingTotal(PaymentSubjectInterface $subject, array $results, array $amounts): void
    {
        foreach ($results as $currency => $expected) {
            if (isset($amounts[$currency])) {
                /** @noinspection PhpParamsInspection */
                $this->configureAmountCalculator($subject, $amounts[$currency]);
            }
            $this->assertEquals($expected, $this->paymentCalculator->calculateRemainingTotal($subject, $currency));
        }
    }

    public function provide_calculateRemainingTotal(): \Generator
    {
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0);
        yield [$subject, ['EUR' => 100, 'USD' => 125], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 40, 0, 0);
        $this->createPayment($subject, 'EUR', 40, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 40, 0, 0);
        $this->createPayment($subject, 'USD', 50, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 100, 0, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 0, 'USD' => 0], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 100, 0, 0);
        $this->createPayment($subject, 'USD', 125, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 0, 'USD' => 0], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 40, 0);
        $this->createPayment($subject, 'EUR', 40, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 40, 0);
        $this->createPayment($subject, 'USD', 50, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 100, 'USD' => 125], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 100, 0);
        $this->createPayment($subject, 'USD', 125, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 100, 'USD' => 125], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 40);
        $this->createPayment($subject, 'EUR', 40, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 40);
        $this->createPayment($subject, 'USD', 50, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 100);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 0, 'USD' => 0], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 100);
        $this->createPayment($subject, 'USD', 125, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 0, 'USD' => 0], ['USD' => 125]];


        $subject = $this->createSubject('EUR', 100, 0, 30, 30, 0);
        $this->createPayment($subject, 'EUR', 30, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        $this->createPayment($subject, 'EUR', 30, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 40, 'USD' => 50], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 30, 30, 0);
        $this->createPayment($subject, 'EUR', 30, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        $this->createPayment($subject, 'USD', 37.5, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 40, 'USD' => 50], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 30, 0, 30);
        $this->createPayment($subject, 'EUR', 30, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        $this->createPayment($subject, 'EUR', 30, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 40, 'USD' => 50], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 30, 30);
        $this->createPayment($subject, 'EUR', 30, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        $this->createPayment($subject, 'EUR', 30, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 40, 'USD' => 50], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 0, 0, 30, 30);
        $this->createPayment($subject, 'USD', 37.5, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        $this->createPayment($subject, 'USD', 37.5, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 40, 'USD' => 50], ['USD' => 125]];


        $subject = $this->createSubject('EUR', 100, 40, 0, 0, 0);
        yield [$subject, ['EUR' => 40, 'USD' => 50], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 40, 0, 0);
        $this->createPayment($subject, 'EUR', 40, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 40, 0, 0);
        $this->createPayment($subject, 'USD', 50, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 40, 60, 0);
        $this->createPayment($subject, 'EUR', 40, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        $this->createPayment($subject, 'EUR', 60, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 40, 60, 0);
        $this->createPayment($subject, 'USD', 50, 1, PaymentStates::STATE_PENDING, self::MANUAL);
        $this->createPayment($subject, 'EUR', 60, 1, PaymentStates::STATE_CAPTURED, self::OUTSTANDING);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 0, 0, 40);
        $this->createPayment($subject, 'EUR', 40, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 0, 0, 40);
        $this->createPayment($subject, 'USD', 50, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 60, 'USD' => 75], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 0, 0, 60);
        $this->createPayment($subject, 'EUR', 60, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 40, 'USD' => 50], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 0, 0, 60);
        $this->createPayment($subject, 'USD', 75, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 40, 'USD' => 50], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 0, 0, 100);
        $this->createPayment($subject, 'EUR', 100, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 0, 'USD' => 0], ['USD' => 125]];

        $subject = $this->createSubject('EUR', 100, 40, 0, 0, 100);
        $this->createPayment($subject, 'USD', 125, 1, PaymentStates::STATE_CAPTURED);
        yield [$subject, ['EUR' => 0, 'USD' => 0], ['USD' => 125]];
    }

    /**
     * @param SaleInterface $subject
     * @param float         $total
     */
    private function configureAmountCalculator(SaleInterface $subject, float $total): void
    {
        $result = $this->createMock(Amount::class);
        $result->method('getTotal')->willReturn($total);

        $this->amountCalculator->method('calculateSale')->with($subject)->willReturn($result);
        $this->amountCalculator->expects($this->once())->method('calculateSale');
    }

    /**
     * @param string $currency
     * @param float  $grand
     * @param float  $deposit
     * @param float  $pending
     * @param float  $outstanding
     * @param float  $paid
     *
     * @return SaleInterface
     */
    private function createSubject(
        string $currency,
        float $grand,
        float $deposit,
        float $pending,
        float $outstanding,
        float $paid
    ): SaleInterface {
        return (new Order())
            ->setCurrency($this->createCurrency($currency))
            ->setGrandTotal($grand)
            ->setDepositTotal($deposit)
            ->setPendingTotal($pending)
            ->setOutstandingAccepted($outstanding)
            ->setPaidTotal($paid);
    }

    /**
     * @param PaymentSubjectInterface $subject
     * @param string $currency
     * @param float  $amount
     * @param float  $rate
     * @param string $state
     * @param int $method
     */
    private function createPayment(
        PaymentSubjectInterface $subject,
        string $currency,
        float $amount,
        float $rate,
        string $state = PaymentStates::STATE_CAPTURED,
        int $method = self::DEFAULT
    ) {
        $subject->addPayment(
            (new OrderPayment())
                ->setCurrency($this->createCurrency($currency))
                ->setMethod($this->createMethod($method))
                ->setState($state)
                ->setAmount($amount)
                ->setExchangeRate($rate)
        );
    }

    /**
     * @param int $type
     *
     * @return PaymentMethodInterface
     */
    private function createMethod(int $type): PaymentMethodInterface
    {
        if (isset($this->methods[$type])) {
            return $this->methods[$type];
        }

        /** @var PaymentMethodInterface|MockObject $method */
        $method = $this->createMock(PaymentMethodInterface::class);
        if ($type === self::DEFAULT) {
            $method->method('isOutstanding')->willReturn(false);
            $method->method('isManual')->willReturn(false);
        } elseif ($type === self::MANUAL) {
            $method->method('isOutstanding')->willReturn(false);
            $method->method('isManual')->willReturn(true);
        } elseif ($type === self::OUTSTANDING) {
            $method->method('isOutstanding')->willReturn(true);
            $method->method('isManual')->willReturn(false);
        } else {
            throw new \UnexpectedValueException();
        }

        return $this->methods[$type] = $method;
    }

    /**
     * @param string $code
     *
     * @return CurrencyInterface
     */
    private function createCurrency(string $code): CurrencyInterface
    {
        if (isset($this->currencies[$code])) {
            return $this->currencies[$code];
        }

        /** @var CurrencyInterface|MockObject $currency */
        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getCode')->willReturn($code);

        return $this->currencies[$code] = $currency;
    }
}
