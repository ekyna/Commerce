<?php

namespace Ekyna\Component\Commerce\Tests\Invoice\Resolver;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Currency\ArrayCurrencyConverter;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoicePayment;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolver;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoicePaymentResolverInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class InvoicePaymentResolverTest
 * @package Ekyna\Component\Commerce\Tests\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoicePaymentResolverTest extends TestCase
{
    private const CURRENCY = 'USD';

    /**
     * @var InvoicePaymentResolverInterface
     */
    private static $resolver;

    /**
     * @var InvoiceInterface[]
     */
    private $invoices;

    /**
     * @var PaymentInterface[]
     */
    private $payments;

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass()
    {
        $converter = new ArrayCurrencyConverter([
            'EUR/USD' => 1.25,
            'USD/EUR' => 0.80,
        ]);

        self::$resolver = new InvoicePaymentResolver($converter, self::CURRENCY);
    }

    /**
     * @param InvoiceInterface $invoice
     * @param array $expected
     *
     * @dataProvider provide_test_resolve
     */
    public function test_resolve(InvoiceInterface $invoice, array $expected)
    {
        $this->assertEquals($expected, self::$resolver->resolve($invoice));
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
        // <invoice index> => [<payment index> => <amount>]
        yield from $this->buildResult([
            0 => [0 => 300, 1 => 200],
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
            0 => [1 => 200],
            1 => [0 => 300, 2 => 200],
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
            0 => [0 => 100],
            1 => [0 => 200],
            2 => [1 => 50],
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
            0 => [0 => 75],
            1 => [],
            2 => [0 => 25],
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
            0 => [0 => 20],
            1 => [1 => 90],
            2 => [0 => 70],
            3 => [1 => 80],
            4 => [1 => 60],
        ]);
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
            'currency' => self::CURRENCY,
            'invoices' => [],
            'payments' => [],
        ], $data);

        /** @var OrderInterface|MockObject $sale */
        $sale = $this->createMock(OrderInterface::class);
        $sale
            ->method('getCurrency')
            ->willReturn($this->mockCurrency($data['currency']));

        foreach ($data['invoices'] as $datum) {
            $datum = array_replace([
                'total'    => 0,
                'date'     => 'now',
                'currency' => self::CURRENCY,
            ], $datum);

            $this->invoices[] = $this->mockInvoice($sale, $datum['total'], $datum['date'], $datum['currency']);
        }
        $sale
            ->method('getInvoices')
            ->willReturn(new ArrayCollection($this->invoices));

        foreach ($data['payments'] as $datum) {
            $datum = array_replace([
                'amount'   => 0,
                'date'     => 'now',
                'currency' => self::CURRENCY,
            ], $datum);

            $this->payments[] = $this->mockPayment($sale, $datum['amount'], $datum['date'], $datum['currency']);
        }
        $sale
            ->method('getPayments')
            ->willReturn(new ArrayCollection($this->payments));
    }

    private function buildResult(array $map): \Generator
    {
        foreach ($map as $i => $m) {
            yield [
                $this->invoices[$i],
                array_map(function($p, $amount) {
                    $ip = new InvoicePayment();
                    $ip->setPayment($this->payments[$p]);
                    $ip->setAmount($amount);
                    return $ip;
                }, array_keys($m), $m)
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
        $currency
            ->method('getCode')
            ->willReturn($code);

        return $currency;
    }

    /**
     * @param SaleInterface $sale
     * @param float         $total
     * @param string        $date
     * @param string        $currency
     *
     * @return InvoiceInterface|MockObject
     */
    private function mockInvoice(SaleInterface $sale, float $total, string $date, string $currency): InvoiceInterface
    {
        $invoice = $this->createMock(InvoiceInterface::class);

        $invoice
            ->method('getSale')
            ->willReturn($sale);

        $invoice
            ->method('getType')
            ->willReturn(InvoiceTypes::TYPE_INVOICE);

        $invoice
            ->method('getGrandTotal')
            ->willReturn($total);

        $invoice
            ->method('getCreatedAt')
            ->willReturn(new \DateTime($date));

        $invoice
            ->method('getCurrency')
            ->willReturn($currency);

        return $invoice;
    }

    /**
     * @param SaleInterface $sale
     * @param float         $amount
     * @param string        $date
     * @param string|null   $currency
     *
     * @return PaymentInterface|MockObject
     * @throws \Exception
     */
    private function mockPayment(SaleInterface $sale, float $amount, string $date, string $currency): PaymentInterface
    {
        $method = $this->createMock(PaymentMethodInterface::class);
        $method
            ->method('isOutstanding')
            ->willReturn(false);

        $payment = $this->createMock(PaymentInterface::class);

        $payment
            ->method('getSale')
            ->willReturn($sale);

        $payment
            ->method('getMethod')
            ->willReturn($method);

        $payment
            ->method('getState')
            ->willReturn(PaymentStates::STATE_CAPTURED);

        $payment
            ->method('getAmount')
            ->willReturn($amount);

        $payment
            ->method('getCompletedAt')
            ->willReturn(new \DateTime($date));

        $payment
            ->method('getCurrency')
            ->willReturn($this->mockCurrency($currency));

        return $payment;
    }
}