<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Payment\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Resolver\PaymentSubjectStateResolver;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionObject;

use function array_intersect_key;
use function array_map;

/**
 * Class PaymentSubjectStateResolverTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentSubjectStateResolverTest extends TestCase
{
    private PaymentCalculatorInterface|MockObject|null        $paymentCalculator;
    private InvoiceSubjectCalculatorInterface|MockObject|null $invoiceSubjectCalculator;
    private ?PaymentSubjectStateResolver                      $resolver;

    protected function setUp(): void
    {
        $this->paymentCalculator = $this->createMock(PaymentCalculatorInterface::class);
        $this->invoiceSubjectCalculator = $this->createMock(InvoiceSubjectCalculatorInterface::class);

        $this->resolver = new PaymentSubjectStateResolver(
            $this->paymentCalculator,
            $this->invoiceSubjectCalculator,
            self::DEFAULT_CURRENCY
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resolver = null;
        $this->paymentCalculator = null;
        $this->invoiceSubjectCalculator = null;
    }

    /**
     * @dataProvider provideResolveState
     */
    public function testResolveState(string $expectedState, array $configuration): void
    {
        $subject = $this->configure($configuration);

        $ro = new ReflectionObject($this->resolver);
        $method = $ro->getMethod('resolveState');

        self::assertEquals($expectedState, $method->invokeArgs($this->resolver, [$subject]));
    }

    public function provideResolveState(): Generator
    {
        yield 0 => [
            PaymentStates::STATE_NEW,
            [],
        ];

        yield 1 => [
            PaymentStates::STATE_NEW,
            ['invoiced' => 50,],
        ];

        yield 2 => [
            PaymentStates::STATE_NEW,
            ['invoiced' => 100,],
        ];

        yield 3 => [
            PaymentStates::STATE_NEW,
            ['invoiced' => 100, 'credited' => 50,],
        ];

        yield 4 => [
            PaymentStates::STATE_NEW,
            ['paid' => 50,],
        ];

        yield 5 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 50, 'invoiced' => 50,],
        ];

        yield 6 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 50, 'invoiced' => 50, 'credited' => 50,],
        ];

        yield 7 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 50, 'invoiced' => 100,],
        ];

        yield 8 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 50, 'invoiced' => 100, 'credited' => 100,],
        ];

        yield 9 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 50, 'refunded' => 50, 'invoiced' => 50,],
        ];

        yield 10 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 50, 'refunded' => 50, 'invoiced' => 100,],
        ];

        yield 11 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 50, 'refunded' => 50, 'invoiced' => 100, 'credited' => 50,],
        ];

        yield 12 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100,],
        ];

        yield 13 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'invoiced' => 50,],
        ];

        yield 14 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'invoiced' => 50, 'credited' => 50,],
        ];

        yield 15 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'invoiced' => 100, 'credited' => 50,],
        ];

        yield 16 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'invoiced' => 100, 'credited' => 100,],
        ];

        yield 17 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'refunded' => 50,],
        ];

        yield 18 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'refunded' => 50, 'invoiced' => 50, 'credited' => 50,],
        ];

        yield 19 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'refunded' => 50, 'invoiced' => 100,],
        ];

        yield 20 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'refunded' => 50, 'invoiced' => 100, 'credited' => 100,],
        ];

        yield 21 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'refunded' => 100, 'invoiced' => 50,],
        ];

        yield 22 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'refunded' => 100, 'invoiced' => 100,],
        ];

        yield 23 => [
            PaymentStates::STATE_CAPTURED,
            ['paid' => 100, 'refunded' => 100, 'invoiced' => 100, 'credited' => 50,],
        ];

        yield 24 => [
            PaymentStates::STATE_COMPLETED,
            ['paid' => 50, 'invoiced' => 100, 'credited' => 50,],
        ];

        yield 25 => [
            PaymentStates::STATE_COMPLETED,
            ['paid' => 100, 'invoiced' => 100,],
        ];

        yield 26 => [
            PaymentStates::STATE_COMPLETED,
            ['paid' => 100, 'refunded' => 50, 'invoiced' => 50,],
        ];

        yield 27 => [
            PaymentStates::STATE_COMPLETED,
            ['paid' => 100, 'refunded' => 50, 'invoiced' => 100, 'credited' => 50,],
        ];

        yield 28 => [
            PaymentStates::STATE_REFUNDED,
            ['paid' => 50, 'refunded' => 50,],
        ];

        yield 29 => [
            PaymentStates::STATE_REFUNDED,
            ['paid' => 50, 'refunded' => 50, 'invoiced' => 50, 'credited' => 50,],
        ];

        yield 30 => [
            PaymentStates::STATE_REFUNDED,
            ['paid' => 50, 'refunded' => 50, 'invoiced' => 100, 'credited' => 100,],
        ];

        yield 31 => [
            PaymentStates::STATE_REFUNDED,
            ['paid' => 100, 'refunded' => 100,],
        ];

        yield 32 => [
            PaymentStates::STATE_REFUNDED,
            ['paid' => 100, 'refunded' => 100, 'invoiced' => 50, 'credited' => 50,],
        ];

        yield 33 => [
            PaymentStates::STATE_REFUNDED,
            ['paid' => 100, 'refunded' => 100, 'invoiced' => 100, 'credited' => 100,],
        ];

        yield 34 => [
            PaymentStates::STATE_NEW,
            ['invoiced' => 50, 'credited' => 50,],
        ];

        yield 35 => [
            PaymentStates::STATE_NEW,
            ['invoiced' => 100, 'credited' => 100,],
        ];

        yield 'No items' => [
            PaymentStates::STATE_NEW,
            [
                'hasItems' => false,
            ],
        ];

        yield 'No payments' => [
            PaymentStates::STATE_NEW,
            [
                'hasPayments' => false,
            ],
        ];

        yield 'No payments, Grand total equals zero' => [
            PaymentStates::STATE_COMPLETED,
            [
                'total'       => 0,
                'hasPayments' => false,
            ],
        ];

        yield 'No payments and fully credited invoices' => [
            PaymentStates::STATE_NEW,
            [
                'invoiced'    => 100,
                'credited'    => 100,
                'hasPayments' => false,
            ],
        ];

        yield 'Accepted outstanding = Total' => [
            PaymentStates::STATE_CAPTURED,
            [
                'accepted' => 100,
            ],
        ];

        yield 'Paid = Deposit' => [
            PaymentStates::STATE_DEPOSIT,
            [
                'paid'    => 50,
                'deposit' => 50,
            ],
        ];

        yield 'Expired > 0' => [
            PaymentStates::STATE_OUTSTANDING,
            [
                'expired' => 50,
            ],
        ];

        yield 'Pending = Total' => [
            PaymentStates::STATE_PENDING,
            [
                'pending' => 100,
            ],
        ];

        yield 'Pending = Deposit' => [
            PaymentStates::STATE_PENDING,
            [
                'deposit' => 50,
                'pending' => 50,
            ],
        ];

        yield 'Paid + Pending = Total' => [
            PaymentStates::STATE_PENDING,
            [
                'paid'    => 50,
                'pending' => 50,
            ],
        ];

        yield 'Failed = Total' => [
            PaymentStates::STATE_FAILED,
            [
                'failed' => 100,
            ],
        ];

        yield 'Canceled = Total' => [
            PaymentStates::STATE_CANCELED,
            [
                'canceled' => 100,
            ],
        ];

        yield 'USD Paid = Total (not fully invoiced)' => [
            PaymentStates::STATE_CAPTURED,
            [
                'currency'     => Fixture::CURRENCY_USD,
                'invoiceState' => InvoiceStates::STATE_PARTIAL,
                'total'        => 125,
                'paid'         => 125,
                'invoiced'     => 80,
            ],
        ];

        yield 'USD Paid = Total (fully invoiced)' => [
            PaymentStates::STATE_COMPLETED,
            [
                'currency'     => Fixture::CURRENCY_USD,
                'invoiceState' => InvoiceStates::STATE_COMPLETED,
                'total'        => 125,
                'paid'         => 125,
                'invoiced'     => 125,
            ],
        ];
    }

    private function configure(array $values = []): SaleInterface|MockObject
    {
        $values = array_replace([
            'currency'     => self::DEFAULT_CURRENCY,
            'hasItems'     => true,
            'hasPayments'  => true,
            'invoiceState' => InvoiceStates::STATE_NEW,

            'total'    => 100,
            'paid'     => 0,
            'refunded' => 0,
            'pending'  => 0,
            'deposit'  => 0,

            'accepted' => 0,
            'expired'  => 0,
            'failed'   => 0,
            'canceled' => 0,

            'invoiced' => 0,
            'credited' => 0,
        ], $values);

        $values = array_map(fn($v) => is_int($v) ? new Decimal($v) : $v, $values);

        $amounts = array_intersect_key($values, [
            'total'    => 0,
            'paid'     => 0,
            'refunded' => 0,
            'pending'  => 0,
            'deposit'  => 0,
        ]);
        $this->paymentCalculator->method('getPaymentAmounts')->willReturn($amounts);

        $this->paymentCalculator->method('calculateOutstandingAcceptedTotal')->willReturn($values['accepted']);
        $this->paymentCalculator->method('calculateOutstandingExpiredTotal')->willReturn($values['expired']);
        $this->paymentCalculator->method('calculateFailedTotal')->willReturn($values['failed']);
        $this->paymentCalculator->method('calculateCanceledTotal')->willReturn($values['canceled']);

        $this->invoiceSubjectCalculator->method('calculateInvoiceTotal')->willReturn($values['invoiced']);
        $this->invoiceSubjectCalculator->method('calculateCreditTotal')->willReturn($values['credited']);


        $subject = $this->createMock(OrderInterface::class); // SaleInterface + InvoiceSubjectInterface

        $subject->method('getCurrency')->willReturn(Fixture::currency($values['currency']));
        $subject->method('getOutstandingAccepted')->willReturn($values['accepted']);
        $subject->method('getOutstandingExpired')->willReturn($values['expired']);
        $subject->method('getInvoiceTotal')->willReturn($values['invoiced']);
        $subject->method('getCreditTotal')->willReturn($values['credited']);
        $subject->method('hasItems')->willReturn($values['hasItems']);
        $subject->method('hasPayments')->willReturn($values['hasPayments']);
        $subject->method('getPaymentState')->willReturn(PaymentStates::STATE_NEW);
        $subject->method('getInvoiceState')->willReturn($values['invoiceState']);
        $subject->method('isFullyInvoiced')->willReturn($values['invoiced'] >= $values['total']);

        return $subject;
    }
}
