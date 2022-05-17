<?php
/** @noinspection PhpMethodNamingConventionInspection */
/** @noinspection PhpTooManyParametersInspection */
declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Invoice\Resolver;

use DateTime;
use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoicePayment;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolver;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Uid\Uuid;

/**
 * Class InvoicePaymentResolverTest
 * @package Ekyna\Component\Commerce\Tests\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaymentResolverTest extends TestCase
{
    /**
     * @var InvoicePaymentResolverInterface
     */
    private $resolver;

    /**
     * @var InvoiceInterface[]
     */
    private array $invoices;

    /**
     * @var PaymentInterface[]
     */
    private array $payments;

    protected function setUp(): void
    {
        $this->resolver = new InvoicePaymentResolver($this->getCurrencyConverter());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resolver = null;
    }

    /**
     * @param InvoiceInterface $invoice
     * @param array            $expected
     *
     * @dataProvider provide_test_resolve
     */
    public function test_resolve(InvoiceInterface $invoice, array $expected)
    {
        $result = $this->resolver->resolve($invoice);

        self::assertEquals($expected, $result);
    }

    public function provide_test_resolve(): Generator
    {
        $this->buildData([
            'invoices' => [
                ['total' => '500', 'date' => '-3 days'],
            ],
            'payments' => [
                ['amount' => '300', 'date' => '-1 days'],
                ['amount' => '200', 'date' => 'now'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['300', '300', 0], ['200', '200', 1]], // #0
        ]);

        $this->buildData([
            'invoices' => [
                ['total' => '200', 'date' => '-3 days'],
                ['total' => '500', 'date' => '-3 days'],
            ],
            'payments' => [
                ['amount' => '300', 'date' => '-2 days'],
                ['amount' => '200', 'date' => '-1 days'],
                ['amount' => '200', 'date' => 'now'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['200', '200', 1]],                // #1
            1 => [['300', '300', 0], ['200', '200', 2]], // #2
        ]);

        $this->buildData([
            'invoices' => [
                ['total' => '100', 'date' => '-3 days'],
                ['total' => '200', 'date' => '-2 days'],
                ['total' => '100', 'date' => '-1 days'],
            ],
            'payments' => [
                ['amount' => '300', 'date' => '-1 days'],
                ['amount' => '50', 'date' => 'now'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['100', '100', 0]], // #3
            1 => [['200', '200', 0]], // #4
            2 => [['50', '50', 1]],   // #5
        ]);

        $this->buildData([
            'invoices' => [
                ['total' => '75', 'date' => '-3 days'],
                ['total' => '50', 'date' => '-2 days'],
                ['total' => '25', 'date' => '-1 days'],
            ],
            'payments' => [
                ['amount' => '100', 'date' => '-1 days'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['75', '75', 0]], // #6
            1 => [],            // #7
            2 => [['25', '25', 0]], // #8
        ]);


        $this->buildData([
            'invoices' => [
                ['total' => '20', 'date' => '-1 days'],
                ['total' => '90', 'date' => '-5 days'],
                ['total' => '70', 'date' => '-3 days'],
                ['total' => '80', 'date' => '-4 days'],
                ['total' => '60', 'date' => '-2 days'],
            ],
            'payments' => [
                ['amount' => '170', 'date' => '-2 days'],
                ['amount' => '150', 'date' => '-1 days'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['20', '20', 1]], // #9
            1 => [['90', '90', 1]], // #10
            2 => [['70', '70', 0]], // #11
            3 => [['80', '80', 1]], // #12
            4 => [['60', '60', 1]], // #13
        ]);


        $this->buildData([
            'invoices' => [
                ['total' => '500', 'date' => '-3 days'],
                ['total' => '300', 'date' => '-2 days', 'credit' => true],
            ],
            'payments' => [
                ['amount' => '200', 'date' => 'now'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['200', '200', 0], ['300', '300', 0, true]], // #14
        ]);


        $this->buildData([
            'invoices' => [
                ['total' => '100', 'date' => '-3 days'],
            ],
            'payments' => [
                ['amount' => '100', 'date' => '-2 days'],
                ['amount' => '100', 'date' => '-2 days', 'refund' => true],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['100', '100', 0], ['-100', '-100', 1]], // #15
        ]);


        $this->buildData([
            'invoices' => [
                ['total' => '100', 'date' => '-3 days'],
            ],
            'payments' => [
                ['amount' => '150', 'date' => '-2 days'],
                ['amount' => '50', 'date' => '-2 days', 'refund' => true],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['100', '100', 0]], // #16
        ]);

        $this->buildData([
            'invoices' => [
                ['total' => '870.70', 'date' => '-45 days'],
                ['total' => '1377.68', 'date' => '-10 days'],
            ],
            'payments' => [
                ['amount' => '870.70', 'date' => '-60 days', 'outstanding' => true, 'state' => PaymentStates::STATE_CANCELED],
                ['amount' => '870.70', 'date' => '-55 days'],
                ['amount' => '1377.68', 'date' => '-45 days', 'outstanding' => true, 'state' => PaymentStates::STATE_CANCELED],
                ['amount' => '1377.68', 'date' => '-42 days'],
                ['amount' => '870.70', 'date' => '-10 days', 'refund' => true],
                ['amount' => '1377.68', 'date' => '-10 days', 'refund' => true],
                ['amount' => '100', 'date' => '-2 days'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['870.70', '870.70', 1], ['-770.70', '-770.70', 4], ['-100.00', '-100.00', 6]], // #17
            1 => [['1377.68', '1377.68', 3], ['-1277.68', '-1277.68', 5]], // #17
        ]);

        // --- USD ---

        $this->buildData([
            'currency'      => Fixture::CURRENCY_USD,
            'exchange_rate' => '1.25',
            'invoices'      => [
                ['total' => '500', 'real_total' => '400', 'date' => '-3 days', 'currency' => Fixture::CURRENCY_USD],
            ],
            'payments'      => [
                ['amount' => '300', 'date' => '-1 days', 'currency' => Fixture::CURRENCY_USD],
                ['amount' => '200', 'date' => 'now', 'currency' => Fixture::CURRENCY_USD],
            ],
        ]);
        yield from $this->buildResult([
            0 => [['300', '240', 0], ['200', '160', 1]], // #17
        ]);
    }

    public function test_resolve_withUnexpectedPaymentCurrency()
    {
        $sale = $this->createMock(OrderInterface::class);
        $sale->method('getCurrency')->willReturn($this->mockCurrency(Fixture::CURRENCY_USD));
        $sale->method('getExchangeRate')->willReturn(new Decimal('1.25'));
        $sale->method('getExchangeDate')->willReturn(new DateTime());

        $sale->method('getInvoices')->willReturn(new ArrayCollection([
            $invoice = $this->mockInvoice(
                $sale,
                '300',
                '240',
                'now',
                Fixture::CURRENCY_USD,
                false
            ),
        ]));

        $sale->method('getPayments')->willReturn(new ArrayCollection([
            $this->mockPayment(
                $sale,
                '200',
                'now',
                Fixture::CURRENCY_GBP,
                PaymentStates::STATE_CAPTURED,
                false,
                false
            ),
        ]));

        $this->expectException(RuntimeException::class);

        $this->resolver->resolve($invoice);
    }

    /**
     * Builds the test data.
     *
     * @param array $data
     */
    private function buildData(array $data): void
    {
        $this->invoices = [];
        $this->payments = [];

        $data = array_replace([
            'currency'      => self::DEFAULT_CURRENCY,
            'exchange_rate' => '1.0',
            'exchange_date' => new DateTime(),
            'invoices'      => [],
            'payments'      => [],
        ], $data);

        /** @var OrderInterface|MockObject $sale */
        $sale = $this->createMock(OrderInterface::class);
        $sale->method('getCurrency')->willReturn($this->mockCurrency($data['currency']));
        $sale->method('getExchangeRate')->willReturn(new Decimal($data['exchange_rate']));
        $sale->method('getExchangeDate')->willReturn($data['exchange_date']);
        $sale->method('getRuntimeUid')->willReturn(Uuid::v4()->toRfc4122());

        foreach ($data['invoices'] as $datum) {
            $datum = array_replace([
                'total'      => '0',
                'real_total' => null,
                'date'       => 'now',
                'currency'   => self::DEFAULT_CURRENCY,
                'credit'     => false,
            ], $datum);

            if (null === $datum['real_total']) {
                $datum['real_total'] = $datum['total'];
            }

            $this->invoices[] = $this->mockInvoice(
                $sale,
                $datum['total'],
                $datum['real_total'],
                $datum['date'],
                $datum['currency'],
                $datum['credit']
            );
        }

        $sale->method('getInvoices')->willReturn(new ArrayCollection($this->invoices));

        foreach ($data['payments'] as $datum) {
            $datum = array_replace([
                'amount'      => '0',
                'date'        => 'now',
                'currency'    => self::DEFAULT_CURRENCY,
                'state'       => PaymentStates::STATE_CAPTURED,
                'outstanding' => false,
                'refund'      => false,
            ], $datum);

            $this->payments[] = $this->mockPayment(
                $sale,
                $datum['amount'],
                $datum['date'],
                $datum['currency'],
                $datum['state'],
                $datum['outstanding'],
                $datum['refund']
            );
        }

        $sale->method('getPayments')->willReturn(new ArrayCollection($this->payments));
    }

    /**
     * @param array $map array<int, array<string, string, int, bool>>
     *
     * @return Generator
     */
    private function buildResult(array $map): Generator
    {
        foreach ($map as $i => $m) {
            yield [
                $this->invoices[$i],
                array_map(function ($amounts) {
                    $ip = new InvoicePayment();
                    $ip->setAmount(new Decimal($amounts[0]));
                    $ip->setRealAmount(new Decimal($amounts[1]));
                    if (isset($amounts[3]) && $amounts[3]) {
                        $ip->setInvoice($this->invoices[$amounts[2]]);
                    } else {
                        $ip->setPayment($this->payments[$amounts[2]]);
                    }

                    return $ip;
                }, $m),
            ];
        }
    }

    /**
     * @return CurrencyInterface|MockObject
     */
    private function mockCurrency(string $code): CurrencyInterface
    {
        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getCode')->willReturn($code);

        return $currency;
    }

    /**
     * @return InvoiceInterface|MockObject
     */
    private function mockInvoice(
        SaleInterface $sale,
        string $total,
        string $realTotal,
        string $date,
        string $currency,
        bool $credit
    ): InvoiceInterface {
        $invoice = $this->createMock(InvoiceInterface::class);
        $invoice->method('getSale')->willReturn($sale);
        $invoice->method('isCredit')->willReturn($credit);
        $invoice->method('getGrandTotal')->willReturn(new Decimal($total));
        $invoice->method('getRealGrandTotal')->willReturn(new Decimal($realTotal));
        $invoice->method('getCreatedAt')->willReturn(new DateTime($date));
        $invoice->method('getCurrency')->willReturn($currency);
        $invoice->method('getRuntimeUid')->willReturn(Uuid::v4()->toRfc4122());

        return $invoice;
    }

    /**
     * @return PaymentInterface|MockObject
     */
    private function mockPayment(
        SaleInterface $sale,
        string $amount,
        string $date,
        string $currency,
        string $state,
        bool $outstanding,
        bool $refund
    ): PaymentInterface {
        $method = $this->createMock(PaymentMethodInterface::class);
        $method->method('isOutstanding')->willReturn($outstanding);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getSale')->willReturn($sale);
        $payment->method('getMethod')->willReturn($method);
        $payment->method('getState')->willReturn($state);
        $payment->method('isRefund')->willReturn($refund);
        $payment->method('getAmount')->willReturn(new Decimal($amount));
        $payment->method('getCompletedAt')->willReturn(new DateTime($date));
        $payment->method('getCurrency')->willReturn($this->mockCurrency($currency));

        return $payment;
    }
}
