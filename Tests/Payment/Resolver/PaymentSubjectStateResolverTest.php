<?php

namespace Ekyna\Component\Commerce\Tests\Payment\Resolver;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Currency\ArrayCurrencyConverter;
use Ekyna\Component\Commerce\Common\Currency\CurrencyConverterInterface;
use Ekyna\Component\Commerce\Common\Model\Amount;
use Ekyna\Component\Commerce\Common\Model\CurrencyInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Resolver\PaymentSubjectStateResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class PaymentSubjectStateResolverTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentSubjectStateResolverTest extends TestCase
{
    /**
     * @var AmountCalculatorInterface|MockObject
     */
    private $amountCalculator;

    /**
     * @var PaymentCalculatorInterface|MockObject
     */
    private $paymentCalculator;

    /**
     * @var CurrencyConverterInterface
     */
    private $converter;

    /**
     * @var PaymentSubjectStateResolver
     */
    private $resolver;

    /**
     * @var CurrencyInterface[]|MockObject[]
     */
    private $currencies = [];


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->converter = new ArrayCurrencyConverter([
            'EUR/USD' => 1.25,
            'USD/EUR' => 0.80,
        ], 'EUR');

        $this->paymentCalculator = $this->createMock(PaymentCalculatorInterface::class);

        $this->amountCalculator = $this->createMock(AmountCalculatorInterface::class);

        $this->resolver = new PaymentSubjectStateResolver(
            $this->amountCalculator,
            $this->paymentCalculator,
            $this->converter
        );
    }

    protected function tearDown(): void
    {
        $this->resolver = null;
        $this->amountCalculator = null;
        $this->paymentCalculator = null;
        $this->converter = null;
    }

    /**
     * @param string        $state
     * @param SaleInterface $subject
     * @param array         $payment
     * @param array         $amount
     *
     * @dataProvider provide_resolveState
     */
    public function test_resolveState(
        string $state,
        SaleInterface $subject,
        array $payment = null,
        array $amount = null
    ): void {
        if ($payment) {
            call_user_func_array([$this, 'configurePaymentCalculator'], $payment);
        }
        if ($amount) {
            call_user_func_array([$this, 'configureAmountCalculator'], $amount);
        }

        $ro = new \ReflectionObject($this->resolver);
        $method = $ro->getMethod('resolveState');
        $method->setAccessible(true);

        $this->assertEquals($state, $method->invokeArgs($this->resolver, [$subject]));
    }

    public function provide_resolveState(): \Generator
    {
        // Subject [Currency, Grand, Deposit, Paid, Pending, Accepted, Expired, Rate, HasPayments, InvoiceState]
        // Calculator [Subject, Paid, Pending, Accepted, Expired, Refunded, Failed, Canceled]

        // 0) No payments
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0, 0, 1, false);
        yield [PaymentStates::STATE_NEW, $subject];

        // 1) No payments and fully credited invoices
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0, 0, 1, false, InvoiceStates::STATE_CREDITED);
        yield [PaymentStates::STATE_CANCELED, $subject];

        // 2) Paid = Grand
        $subject = $this->createSubject('EUR', 100, 0, 100, 0, 0, 0);
        yield [PaymentStates::STATE_COMPLETED, $subject];

        // 3) Accepted outstanding = Grand
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 100, 0);
        yield [PaymentStates::STATE_CAPTURED, $subject];

        // 4) Paid = Deposit
        $subject = $this->createSubject('EUR', 100, 50, 50, 0, 0, 0);
        yield [PaymentStates::STATE_DEPOSIT, $subject];

        // 5) Expired > 0
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0, 50);
        yield [PaymentStates::STATE_OUTSTANDING, $subject];

        // 6) Paid + Accepted + Pending = Grand
        $subject = $this->createSubject('EUR', 100, 0, 0, 50, 50, 0);
        yield [PaymentStates::STATE_PENDING, $subject];

        // 7) Refunded = Grand
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0, 0);
        $payment = [$subject, 0, 0, 0, 0, 100, 0, 0];
        yield [PaymentStates::STATE_REFUNDED, $subject, $payment];

        // 8) Payments (but ot paid/accepted/pending) and fully credited invoices
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0, 0, 1, true, InvoiceStates::STATE_CREDITED);
        $payment = [$subject, 0, 0, 0, 0, 0, 0, 0];
        yield [PaymentStates::STATE_CANCELED, $subject, $payment];

        // 9) Failed = Grand
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0, 0);
        $payment = [$subject, 0, 0, 0, 0, 0, 100, 0];
        yield [PaymentStates::STATE_FAILED, $subject, $payment];

        // 10) Canceled = Grand
        $subject = $this->createSubject('EUR', 100, 0, 0, 0, 0, 0);
        $payment = [$subject, 0, 0, 0, 0, 0, 0, 100];
        yield [PaymentStates::STATE_CANCELED, $subject, $payment];

        // 11) USD Paid = Grand
        $subject = $this->createSubject('USD', 100, 0, 100, 0, 0, 0);
        $payment = [$subject, 125, 0, 0, 0, 0, 0, 0];
        $amount = [$subject, 125];
        yield [PaymentStates::STATE_COMPLETED, $subject, $payment, $amount];
    }

    /**
     * @param string $currency
     * @param float  $grand
     * @param float  $deposit
     * @param float  $pending
     * @param float  $accepted
     * @param float  $expired
     * @param float  $paid
     * @param float  $rate
     * @param bool   $hasPayments
     * @param string $invoiceState
     *
     * @return SaleInterface|MockObject
     */
    private function createSubject(
        string $currency,
        float $grand,
        float $deposit,
        float $paid,
        float $pending,
        float $accepted,
        float $expired,
        float $rate = 1,
        bool $hasPayments = true,
        string $invoiceState = InvoiceStates::STATE_NEW
    ): SaleInterface {
        $subject = $this->createMock([SaleInterface::class, InvoiceSubjectInterface::class]);

        $subject->method('getCurrency')->willReturn($this->createCurrency($currency));
        $subject->method('getGrandTotal')->willReturn($grand);
        $subject->method('getDepositTotal')->willReturn($deposit);
        $subject->method('getPaidTotal')->willReturn($paid);
        $subject->method('getPendingTotal')->willReturn($pending);
        $subject->method('getOutstandingAccepted')->willReturn($accepted);
        $subject->method('getOutstandingExpired')->willReturn($expired);
        $subject->method('getExchangeRate')->willReturn($rate);
        $subject->method('hasPayments')->willReturn($hasPayments);
        $subject->method('getPaymentState')->willReturn(PaymentStates::STATE_NEW);
        $subject->method('getInvoiceState')->willReturn($invoiceState);

        return $subject;
    }

    /**
     * @param SaleInterface $subject
     * @param float         $paid
     * @param float         $pending
     * @param float         $accepted
     * @param float         $expired
     * @param float         $refunded
     * @param float         $failed
     * @param float         $canceled
     */
    private function configurePaymentCalculator(
        SaleInterface $subject,
        float $paid,
        float $pending,
        float $accepted,
        float $expired,
        float $refunded,
        float $failed,
        float $canceled
    ): void {
        $this->paymentCalculator->method('calculatePaidTotal')->with($subject)->willReturn($paid);
        if ($paid) {
            $this->paymentCalculator->expects($this->once())->method('calculatePaidTotal');
        }
        $this->paymentCalculator->method('calculateOfflinePendingTotal')->with($subject)->willReturn($pending);
        if ($pending) {
            $this->paymentCalculator->expects($this->once())->method('calculateOfflinePendingTotal');
        }
        $this->paymentCalculator->method('calculateOutstandingAcceptedTotal')->with($subject)->willReturn($accepted);
        if ($accepted) {
            $this->paymentCalculator->expects($this->once())->method('calculateOutstandingAcceptedTotal');
        }
        $this->paymentCalculator->method('calculateOutstandingExpiredTotal')->with($subject)->willReturn($expired);
        if ($expired) {
            $this->paymentCalculator->expects($this->once())->method('calculateOutstandingExpiredTotal');
        }
        $this->paymentCalculator->method('calculateRefundedTotal')->with($subject)->willReturn($refunded);
        if ($refunded) {
            $this->paymentCalculator->expects($this->once())->method('calculateRefundedTotal');
        }
        $this->paymentCalculator->method('calculateFailedTotal')->with($subject)->willReturn($failed);
        if ($failed) {
            $this->paymentCalculator->expects($this->once())->method('calculateFailedTotal');
        }
        $this->paymentCalculator->method('calculateCanceledTotal')->with($subject)->willReturn($canceled);
        if ($canceled) {
            $this->paymentCalculator->expects($this->once())->method('calculateCanceledTotal');
        }
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
