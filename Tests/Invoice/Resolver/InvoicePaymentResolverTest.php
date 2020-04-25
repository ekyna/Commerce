<?php

namespace Ekyna\Component\Commerce\Tests\Invoice\Resolver;

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
use PHPUnit\Framework\MockObject\MockObject;

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
    private $invoices;

    /**
     * @var PaymentInterface[]
     */
    private $payments;


    public function setUp(): void
    {
        $this->resolver = new InvoicePaymentResolver($this->getCurrencyConverter());
    }

    public function tearDown(): void
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
        $this->assertEquals($expected, $this->resolver->resolve($invoice));
    }

    public function provide_test_resolve(): \Generator
    {
        $this->buildData([
            'invoices' => [
                ['total' => 500, 'date' => '-3 days'],
            ],
            'payments' => [
                ['amount' => 300, 'date' => '-1 days'],
                ['amount' => 200, 'date' => 'now'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [0 => [300, 300], 1 => [200, 200]], // #0
        ]);

        $this->buildData([
            'invoices' => [
                ['total' => 200, 'date' => '-3 days'],
                ['total' => 500, 'date' => '-3 days'],
            ],
            'payments' => [
                ['amount' => 300, 'date' => '-2 days'],
                ['amount' => 200, 'date' => '-1 days'],
                ['amount' => 200, 'date' => 'now'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [1 => [200, 200]],                  // #1
            1 => [0 => [300, 300], 2 => [200, 200]], // #2
        ]);

        $this->buildData([
            'invoices' => [
                ['total' => 100, 'date' => '-3 days'],
                ['total' => 200, 'date' => '-2 days'],
                ['total' => 100, 'date' => '-1 days'],
            ],
            'payments' => [
                ['amount' => 300, 'date' => '-1 days'],
                ['amount' => 50, 'date' => 'now'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [0 => [100, 100]], // #3
            1 => [0 => [200, 200]], // #4
            2 => [1 => [50, 50]],   // #5
        ]);

        $this->buildData([
            'invoices' => [
                ['total' => 75, 'date' => '-3 days'],
                ['total' => 50, 'date' => '-2 days'],
                ['total' => 25, 'date' => '-1 days'],
            ],
            'payments' => [
                ['amount' => 100, 'date' => '-1 days'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [0 => [75, 75]], // #6
            1 => [],              // #7
            2 => [0 => [25, 25]], // #8
        ]);


        $this->buildData([
            'invoices' => [
                ['total' => 20, 'date' => '-1 days'],
                ['total' => 90, 'date' => '-5 days'],
                ['total' => 70, 'date' => '-3 days'],
                ['total' => 80, 'date' => '-4 days'],
                ['total' => 60, 'date' => '-2 days'],
            ],
            'payments' => [
                ['amount' => 170, 'date' => '-2 days'],
                ['amount' => 150, 'date' => '-1 days'],
            ],
        ]);
        yield from $this->buildResult([
            0 => [0 => [20, 20]], // #9
            1 => [1 => [90, 90]], // #10
            2 => [0 => [70, 70]], // #11
            3 => [1 => [80, 80]], // #12
            4 => [1 => [60, 60]], // #13
        ]);

        // --- USD ---

        $this->buildData([
            'currency'      => Fixture::CURRENCY_USD,
            'exchange_rate' => 1.25,
            'invoices'      => [
                ['total' => 500, 'real_total' => 400, 'date' => '-3 days', 'currency' => Fixture::CURRENCY_USD],
            ],
            'payments'      => [
                ['amount' => 300, 'date' => '-1 days', 'currency' => Fixture::CURRENCY_USD],
                ['amount' => 200, 'date' => 'now', 'currency' => Fixture::CURRENCY_USD],
            ],
        ]);
        yield from $this->buildResult([
            0 => [0 => [300, 240], 1 => [200, 160]], // #14
        ]);
    }

    public function test_resolve_withUnexpectedPaymentCurrency()
    {
        $sale = $this->createMock(OrderInterface::class);
        $sale->method('getCurrency')->willReturn($this->mockCurrency(Fixture::CURRENCY_USD));
        $sale->method('getExchangeRate')->willReturn(1.25);
        $sale->method('getExchangeDate')->willReturn(new \DateTime());

        $sale->method('getInvoices')->willReturn(new ArrayCollection([
            $invoice = $this->mockInvoice(
                $sale,
                300,
                240,
                'now',
                Fixture::CURRENCY_USD,
                false
            ),
        ]));

        $sale->method('getPayments')->willReturn(new ArrayCollection([
            $this->mockPayment(
                $sale,
                200,
                'now',
                Fixture::CURRENCY_GBP,
                PaymentStates::STATE_CAPTURED,
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
            'exchange_rate' => 1.0,
            'exchange_date' => new \DateTime(),
            'invoices'      => [],
            'payments'      => [],
        ], $data);

        /** @var OrderInterface|MockObject $sale */
        $sale = $this->createMock(OrderInterface::class);
        $sale->method('getCurrency')->willReturn($this->mockCurrency($data['currency']));
        $sale->method('getExchangeRate')->willReturn($data['exchange_rate']);
        $sale->method('getExchangeDate')->willReturn($data['exchange_date']);

        foreach ($data['invoices'] as $datum) {
            $datum = array_replace([
                'total'      => 0,
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
                'amount'      => 0,
                'date'        => 'now',
                'currency'    => self::DEFAULT_CURRENCY,
                'state'       => PaymentStates::STATE_CAPTURED,
                'outstanding' => false,
            ], $datum);

            $this->payments[] = $this->mockPayment(
                $sale,
                $datum['amount'],
                $datum['date'],
                $datum['currency'],
                $datum['state'],
                $datum['outstanding']
            );
        }

        $sale->method('getPayments')->willReturn(new ArrayCollection($this->payments));
    }

    /**
     * @param array $map
     *
     * @return \Generator <invoice index> => [<payment index> => [<amount>, <realAmount>]]
     */
    private function buildResult(array $map): \Generator
    {
        foreach ($map as $i => $m) {
            yield [
                $this->invoices[$i],
                array_map(function ($p, $amounts) {
                    $ip = new InvoicePayment();
                    $ip->setPayment($this->payments[$p]);
                    $ip->setAmount($amounts[0]);
                    $ip->setRealAmount($amounts[1]);

                    return $ip;
                }, array_keys($m), $m),
            ];
        }
    }

    /**
     * @param string $code
     *
     * @return CurrencyInterface|MockObject
     */
    private function mockCurrency(string $code): CurrencyInterface
    {
        $currency = $this->createMock(CurrencyInterface::class);
        $currency->method('getCode')->willReturn($code);

        return $currency;
    }

    /**
     * @param SaleInterface $sale
     * @param float         $total
     * @param float         $realTotal
     * @param string        $date
     * @param string        $currency
     * @param bool          $credit
     *
     * @return InvoiceInterface|MockObject
     */
    private function mockInvoice(
        SaleInterface $sale,
        float $total,
        float $realTotal,
        string $date,
        string $currency,
        bool $credit
    ): InvoiceInterface {
        $invoice = $this->createMock(InvoiceInterface::class);
        $invoice->method('getSale')->willReturn($sale);
        $invoice->method('isCredit')->willReturn($credit);
        $invoice->method('getGrandTotal')->willReturn($total);
        $invoice->method('getRealGrandTotal')->willReturn($realTotal);
        $invoice->method('getCreatedAt')->willReturn(new \DateTime($date));
        $invoice->method('getCurrency')->willReturn($currency);

        return $invoice;
    }

    /**
     * @param SaleInterface $sale
     * @param float         $amount
     * @param string        $date
     * @param string        $currency
     * @param string        $state
     * @param bool          $outstanding
     *
     * @return PaymentInterface|MockObject
     * @throws \Exception
     */
    private function mockPayment(
        SaleInterface $sale,
        float $amount,
        string $date,
        string $currency,
        string $state,
        bool $outstanding
    ): PaymentInterface {
        $method = $this->createMock(PaymentMethodInterface::class);
        $method->method('isOutstanding')->willReturn($outstanding);

        $payment = $this->createMock(PaymentInterface::class);
        $payment->method('getSale')->willReturn($sale);
        $payment->method('getMethod')->willReturn($method);
        $payment->method('getState')->willReturn($state);
        $payment->method('getAmount')->willReturn($amount);
        $payment->method('getCompletedAt')->willReturn(new \DateTime($date));
        $payment->method('getCurrency')->willReturn($this->mockCurrency($currency));

        return $payment;
    }
}
