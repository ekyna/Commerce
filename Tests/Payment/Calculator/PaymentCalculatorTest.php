<?php

namespace Ekyna\Component\Commerce\Tests\Payment\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Model\Amount;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculator;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class PaymentCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentCalculatorTest extends TestCase
{
    /**
     * @var AmountCalculatorFactory|MockObject
     */
    private $calculatorFactory;

    /**
     * @var PaymentCalculator
     */
    private $paymentCalculator;


    protected function setUp(): void
    {
        $this->calculatorFactory = $this->createMock(AmountCalculatorFactory::class);
        $this->paymentCalculator = new PaymentCalculator($this->calculatorFactory, $this->getCurrencyConverter());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->calculatorFactory = null;
        $this->paymentCalculator = null;
    }

    /**
     * @param array $data
     * @param array $results
     *
     * @dataProvider provide_calculatePaidTotal
     */
    public function test_calculatePaidTotal(array $data, array $results): void
    {
        $order = Fixture::order($data);

        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculatePaidTotal($order, $currency));
        }
    }

    public function provide_calculatePaidTotal(): \Generator
    {
        yield [
            [],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100, 'state' => PaymentStates::STATE_FAILED],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING, 'amount' => 100,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100,],
                    ['refund' => true, 'amount' => 100,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    ['currency' => Fixture::CURRENCY_USD, 'amount' => 125, 'exchange_rate' => 1.25,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    ['currency' => Fixture::CURRENCY_USD, 'amount' => 100, 'exchange_rate' => 1.25,],
                    ['amount' => 20,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];
    }

    /**
     * @param array $data
     * @param array $results
     *
     * @dataProvider provide_calculateRefundedTotal
     */
    public function test_calculateRefundedTotal(array $data, array $results): void
    {
        $order = Fixture::order($data);

        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculateRefundedTotal($order, $currency));
        }
    }

    public function provide_calculateRefundedTotal(): \Generator
    {
        yield [
            [
                'payments' => [],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_REFUNDED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_CREDIT,
                        'state'  => PaymentStates::STATE_REFUNDED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'state'         => PaymentStates::STATE_REFUNDED,
                        'currency'      => Fixture::CURRENCY_USD,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'state'         => PaymentStates::STATE_REFUNDED,
                        'currency'      => Fixture::CURRENCY_USD,
                        'amount'        => 100,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'state'  => PaymentStates::STATE_REFUNDED,
                        'amount' => 20,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        // Refunds

        yield [
            [
                'payments' => [
                    ['amount' => 100, 'refund' => true,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'amount' => 100,
                        'refund' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_CREDIT,
                        'amount' => 100,
                        'refund' => true,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                        'refund'        => true,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_REFUNDED,
                        'amount'        => 100,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'amount' => 20,
                        'refund' => true,
                    ],
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'amount' => 20,
                        'refund' => true,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 120, Fixture::CURRENCY_USD => 150],
        ];
    }

    /**
     * @param array $data
     * @param array $results
     *
     * @dataProvider provide_calculateOutstandingAcceptedTotal
     */
    public function test_calculateOutstandingAcceptedTotal(array $data, array $results): void
    {
        $order = Fixture::order($data);

        foreach ($results as $currency => $expected) {
            $actual = $this->paymentCalculator->calculateOutstandingAcceptedTotal($order, $currency);
            $this->assertEquals($expected, $actual);
        }
    }

    public function provide_calculateOutstandingAcceptedTotal(): \Generator
    {
        yield [
            [
                'payments' => [],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_FAILED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method'        => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'currency'      => Fixture::CURRENCY_USD,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method'        => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'currency'      => Fixture::CURRENCY_USD,
                        'amount'        => 100,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'amount' => 20,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];
    }

    /**
     * @param array $data
     * @param array $results
     *
     * @dataProvider provide_calculateOutstandingExpiredTotal
     */
    public function test_calculateOutstandingExpiredTotal(array $data, array $results): void
    {
        $order = Fixture::order($data);

        foreach ($results as $currency => $expected) {
            $actual = $this->paymentCalculator->calculateOutstandingExpiredTotal($order, $currency);
            $this->assertEquals($expected, $actual);
        }
    }

    public function provide_calculateOutstandingExpiredTotal(): \Generator
    {
        yield [
            [
                'payments' => [],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100, 'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100, 'state' => PaymentStates::STATE_EXPIRED,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_EXPIRED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method'        => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_EXPIRED,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method'        => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_EXPIRED,
                        'amount'        => 100,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_EXPIRED,
                        'amount' => 20,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];
    }

    /**
     * @param array $data
     * @param array $results
     *
     * @dataProvider provide_calculateFailedTotal
     */
    public function test_calculateFailedTotal(array $data, array $results): void
    {
        $order = Fixture::order($data);

        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculateFailedTotal($order, $currency));
        }
    }

    public function provide_calculateFailedTotal(): \Generator
    {
        yield [
            [
                'payments' => [],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'grand'    => 100,
                'payments' => [
                    ['amount' => 100,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_FAILED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100, 'state' => PaymentStates::STATE_FAILED,],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_FAILED,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_FAILED,
                        'amount'        => 100,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'state'  => PaymentStates::STATE_FAILED,
                        'amount' => 20,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];
    }

    /**
     * @param array $data
     * @param array $results
     *
     * @dataProvider provide_calculateCanceledTotal
     */
    public function test_calculateCanceledTotal(array $data, array $results): void
    {
        $order = Fixture::order($data);

        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculateCanceledTotal($order, $currency));
        }
    }

    public function provide_calculateCanceledTotal(): \Generator
    {
        yield [
            [
                'payments' => [],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_CANCELED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'state'  => PaymentStates::STATE_CANCELED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_CANCELED,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_CANCELED,
                        'amount'        => 100,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'state'  => PaymentStates::STATE_CANCELED,
                        'amount' => 20,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];
    }

    /**
     * @param array $data
     * @param array $results
     *
     * @dataProvider provide_calculateOfflinePendingTotal
     */
    public function test_calculateOfflinePendingTotal(array $data, array $results): void
    {
        $order = Fixture::order($data);

        foreach ($results as $currency => $expected) {
            $this->assertEquals($expected, $this->paymentCalculator->calculateOfflinePendingTotal($order, $currency));
        }
    }

    public function provide_calculateOfflinePendingTotal(): \Generator
    {
        yield [
            [
                'payments' => [],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100, 'method' => Fixture::PAYMENT_METHOD_MANUAL],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    ['amount' => 100, 'state' => PaymentStates::STATE_PENDING],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_PENDING,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method'        => Fixture::PAYMENT_METHOD_MANUAL,
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_PENDING,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];

        yield [
            [
                'payments' => [
                    [
                        'method'        => Fixture::PAYMENT_METHOD_MANUAL,
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_PENDING,
                        'amount'        => 100,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_PENDING,
                        'amount' => 20,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
        ];
    }

    /**
     * @param array $data
     * @param array $results
     * @param array $amounts
     *
     * @dataProvider provide_calculateExpectedPaymentAmount
     */
    public function test_calculateExpectedPaymentAmount(array $data, array $results, array $amounts): void
    {
        $order = Fixture::order($data);

        foreach ($results as $currency => $expected) {
            if (isset($amounts[$currency])) {
                $this->configureAmountFactory($order, $amounts[$currency]);
            }

            $actual = $this->paymentCalculator->calculateExpectedPaymentAmount($order, $currency);

            $this->assertEquals($expected, $actual);
        }
    }

    public function provide_calculateExpectedPaymentAmount(): \Generator
    {
        yield 'Case 1' => [
            [
                'grand_total' => 100,
                'payments'    => [],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 2' => [
            [
                'grand_total'   => 100,
                'pending_total' => 40,
                'payments'      => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_PENDING,
                        'amount' => 40,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 3' => [
            [
                'grand_total'   => 100,
                'pending_total' => 40,
                'payments'      => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'method'        => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'         => PaymentStates::STATE_PENDING,
                        'exchange_rate' => 1.25,
                        'amount'        => 50,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 4' => [
            [
                'grand_total'   => 100,
                'pending_total' => 100,
                'payments'      => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_PENDING,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 5' => [
            [
                'grand_total'   => 100,
                'pending_total' => 100,
                'payments'      => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'method'        => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'         => PaymentStates::STATE_PENDING,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 6' => [
            [
                'grand_total'          => 100,
                'outstanding_accepted' => 40,
                'payments'             => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 40,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 7' => [
            [
                'grand_total'          => 100,
                'outstanding_accepted' => 40,
                'payments'             => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'method'        => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 50,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 8' => [
            [
                'grand_total'          => 100,
                'outstanding_accepted' => 100,
                'payments'             => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 9' => [
            [
                'grand_total'          => 100,
                'outstanding_accepted' => 100,
                'payments'             => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'method'        => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 100, Fixture::CURRENCY_USD => 125],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 10' => [
            [
                'grand_total' => 100,
                'paid_total'  => 40,
                'payments'    => [
                    [
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 40,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 11' => [
            [
                'grand_total' => 100,
                'paid_total'  => 40,
                'payments'    => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 50,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 12' => [
            [
                'grand_total' => 100,
                'paid_total'  => 100,
                'payments'    => [
                    [
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 13' => [
            [
                'grand_total' => 100,
                'paid_total'  => 100,
                'payments'    => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 14' => [
            [
                'grand_total'          => 100,
                'pending_total'        => 30,
                'outstanding_accepted' => 30,
                'payments'             => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_PENDING,
                        'amount' => 30,
                    ],
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 30,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 15' => [
            [
                'grand_total'          => 100,
                'pending_total'        => 30,
                'outstanding_accepted' => 30,
                'payments'             => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_PENDING,
                        'amount' => 30,
                    ],
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'method'        => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 37.5,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 16' => [
            [
                'grand_total'   => 100,
                'pending_total' => 30,
                'paid_total'    => 30,
                'payments'      => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_PENDING,
                        'amount' => 30,
                    ],
                    [
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 30,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 17' => [
            [
                'grand_total'   => 100,
                'pending_total' => 30,
                'paid_total'    => 30,
                'payments'      => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_PENDING,
                        'amount' => 30,
                    ],
                    [
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 30,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 18' => [
            [
                'grand_total'          => 100,
                'outstanding_accepted' => 30,
                'paid_total'           => 30,
                'payments'             => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 30,
                    ],
                    [
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 30,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 19' => [
            [
                'grand_total'          => 100,
                'outstanding_accepted' => 30,
                'paid_total'           => 30,
                'payments'             => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'method'        => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 37.5,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 37.5,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 20' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'payments'      => [],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 21' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'pending_total' => 40,
                'payments'      => [
                    [
                        'method' => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 40,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 22' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'pending_total' => 40,
                'payments'      => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'method'        => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 50,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 23' => [
            [
                'grand_total'          => 100,
                'deposit_total'        => 40,
                'pending_total'        => 40,
                'outstanding_accepted' => 60,
                'payments'             => [
                    [
                        'method'        => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'         => PaymentStates::STATE_PENDING,
                        'amount'        => 40,
                        'exchange_rate' => 1.25,
                    ],
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 60,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 24' => [
            [
                'grand_total'          => 100,
                'deposit_total'        => 40,
                'pending_total'        => 40,
                'outstanding_accepted' => 60,
                'payments'             => [
                    [
                        'currency' => Fixture::CURRENCY_USD,
                        'method'   => Fixture::PAYMENT_METHOD_MANUAL,
                        'state'    => PaymentStates::STATE_PENDING,
                        'amount'   => 50,
                    ],
                    [
                        'method' => Fixture::PAYMENT_METHOD_OUTSTANDING,
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 60,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 25' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'paid_total'    => 40,
                'payments'      => [
                    [
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 40,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 26' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'paid_total'    => 40,
                'payments'      => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 50,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 60, Fixture::CURRENCY_USD => 75],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 27' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'paid_total'    => 60,
                'payments'      => [
                    [
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 60,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 28' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'paid_total'    => 60,
                'payments'      => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 75,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 40, Fixture::CURRENCY_USD => 50],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 29' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'paid_total'    => 100,
                'payments'      => [
                    [
                        'state'  => PaymentStates::STATE_CAPTURED,
                        'amount' => 100,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
            [Fixture::CURRENCY_USD => 125],
        ];

        yield 'Case 30' => [
            [
                'grand_total'   => 100,
                'deposit_total' => 40,
                'paid_total'    => 100,
                'payments'      => [
                    [
                        'currency'      => Fixture::CURRENCY_USD,
                        'state'         => PaymentStates::STATE_CAPTURED,
                        'amount'        => 125,
                        'exchange_rate' => 1.25,
                    ],
                ],
            ],
            [Fixture::CURRENCY_EUR => 0, Fixture::CURRENCY_USD => 0],
            [Fixture::CURRENCY_USD => 125],
        ];
    }

    // TODO Refunds cases
    // TODO Invoices/credits cases

    /**
     * @param SaleInterface $subject
     * @param float         $total
     */
    private function configureAmountFactory(SaleInterface $subject, float $total): void
    {
        $result = $this->createMock(Amount::class);
        $result->method('getTotal')->willReturn($total);

        $calculator = $this->createMock(AmountCalculatorInterface::class);

        $calculator
            ->expects($this->once())
            ->method('calculateSale')
            ->with($subject)
            ->willReturn($result);

        $this->calculatorFactory->method('create')->willReturn($calculator);
    }
}
