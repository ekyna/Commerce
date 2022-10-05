<?php
/** @noinspection PhpTooManyParametersInspection */

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

        $ro = new \ReflectionObject($this->resolver);
        $method = $ro->getMethod('resolveState');

        $this->assertEquals($expectedState, $method->invokeArgs($this->resolver, [$subject]));
    }

    public function provideResolveState(): Generator
    {
        // 0) No payments
        yield 'No payments' => [
            PaymentStates::STATE_NEW,
            [
                'hasPayments' => false,
            ],
        ];

        // 1) No payments and fully credited invoices
        yield 'No payments and fully credited invoices' => [
            PaymentStates::STATE_CANCELED,
            [
                'invoiced'     => 100,
                'credited'     => 100,
                'hasPayments'  => false,
                'invoiceState' => InvoiceStates::STATE_CREDITED,
            ],
        ];

        // 2) Paid = Total and not fully invoiced
        yield 'Paid = Total (not fully invoiced)' => [
            PaymentStates::STATE_CAPTURED,
            [
                'paid'         => 100,
                'invoiced'     => 50,
                'invoiceState' => InvoiceStates::STATE_PARTIAL,
            ],
        ];

        // 3) Paid = Total
        yield 'Paid = Total (fully invoiced)' => [
            PaymentStates::STATE_COMPLETED,
            [
                'paid'         => 100,
                'invoiced'     => 100,
                'invoiceState' => InvoiceStates::STATE_COMPLETED,
            ],
        ];

        // 4) Accepted outstanding = Total
        yield 'Accepted outstanding = Total' => [
            PaymentStates::STATE_CAPTURED,
            [
                'accepted' => 100,
            ],
        ];

        // 5) Paid = Deposit
        yield 'Paid = Deposit' => [
            PaymentStates::STATE_DEPOSIT,
            [
                'paid'    => 50,
                'deposit' => 50,
            ],
        ];

        // 6) Expired > 0
        yield 'Expired > 0' => [
            PaymentStates::STATE_OUTSTANDING,
            [
                'expired' => 50,
            ],
        ];

        // 7) Paid + Pending = Total
        yield 'Paid + Pending = Total' => [
            PaymentStates::STATE_PENDING,
            [
                'paid'    => 50,
                'pending' => 50,
            ],
        ];

        // 8) Paid = Refunded = Total
        yield 'Paid = Refunded = Total (not invoiced)' => [
            PaymentStates::STATE_REFUNDED,
            [
                'paid'     => 100,
                'refunded' => 100,
            ],
        ];

        // 9) Paid = Refunded = Total
        yield 'Paid = Refunded = Total (partially invoiced)' => [
            PaymentStates::STATE_CAPTURED,
            [
                'paid'     => 100,
                'refunded' => 100,
                'invoiced' => 50,
            ],
        ];

        // 10) Paid = Refunded = Total
        yield 'Paid = Refunded = Total (fully invoiced)' => [
            PaymentStates::STATE_REFUNDED,
            [
                'paid'     => 100,
                'refunded' => 100,
                'invoiced' => 100,
                'credited' => 100,
            ],
        ];

        // 11) Failed = Total
        yield 'Failed = Total' => [
            PaymentStates::STATE_FAILED,
            [
                'failed' => 100,
            ],
        ];

        // 12) Canceled = Total
        yield 'Canceled = Total' => [
            PaymentStates::STATE_CANCELED,
            [
                'canceled' => 100,
            ],
        ];

        // 13) USD Paid = Total
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

        // 14) USD Paid = Total
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
        $subject->method('hasPayments')->willReturn($values['hasPayments']);
        $subject->method('getPaymentState')->willReturn(PaymentStates::STATE_NEW);
        $subject->method('getInvoiceState')->willReturn($values['invoiceState']);
        $subject->method('isFullyInvoiced')->willReturn($values['invoiced'] >= $values['total']);

        return $subject;
    }
}
