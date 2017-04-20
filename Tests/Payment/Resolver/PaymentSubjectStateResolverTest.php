<?php
/** @noinspection PhpTooManyParametersInspection */

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Payment\Resolver;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Calculator\PaymentCalculatorInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Payment\Resolver\PaymentSubjectStateResolver;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use function array_map;

/**
 * Class PaymentSubjectStateResolverTest
 * @package Ekyna\Component\Commerce\Tests\Payment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentSubjectStateResolverTest extends TestCase
{
    /**
     * @var PaymentCalculatorInterface|MockObject
     */
    private $paymentCalculator;

    /**
     * @var PaymentSubjectStateResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->paymentCalculator = $this->createMock(PaymentCalculatorInterface::class);

        $this->resolver = new PaymentSubjectStateResolver(
            $this->paymentCalculator,
            $this->getCurrencyConverter()
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resolver = null;
        $this->paymentCalculator = null;
    }

    /**
     * @dataProvider provideResolveState
     */
    public function testResolveState(
        string        $state,
        SaleInterface $subject,
        array         $calculator
    ): void {
        call_user_func_array([$this, 'configurePaymentCalculator'], $calculator);

        $ro = new \ReflectionObject($this->resolver);
        $method = $ro->getMethod('resolveState');
        $method->setAccessible(true);

        $this->assertEquals($state, $method->invokeArgs($this->resolver, [$subject]));
    }

    public function provideResolveState(): \Generator
    {
        // Subject [Currency, Accepted, Expired, HasPayments, InvoiceState]
        // Calculator [Amounts [Total, Paid, Refunded, Deposit, Pending], Accepted, Expired, Failed, Canceled]

        // 0) No payments
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0, false);
        $calculator = [[100, 0, 0, 0, 0], 0, 0, 0, 0];
        yield 'No payments' => [PaymentStates::STATE_NEW, $subject, $calculator];

        // 1) No payments and fully credited invoices
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0, false, InvoiceStates::STATE_CREDITED, true);
        $calculator = [[0, 0, 0, 0, 0], 0, 0, 0, 0];
        yield 'No payments and fully credited invoices' => [PaymentStates::STATE_CANCELED, $subject, $calculator];

        // 2) Paid = Grand and not fully invoiced
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0, true, InvoiceStates::STATE_PARTIAL, false);
        $calculator = [[100, 100, 0, 0, 0], 0, 0, 0, 0];
        yield 'Paid = Grand (not fully invoiced)' => [PaymentStates::STATE_CAPTURED, $subject, $calculator];

        // 3) Paid = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0, true, InvoiceStates::STATE_COMPLETED, true);
        $calculator = [[100, 100, 0, 0, 0], 0, 0, 0, 0];
        yield 'Paid = Grand (fully invoiced)' => [PaymentStates::STATE_COMPLETED, $subject, $calculator];

        // 4) Accepted outstanding = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 100, 0);
        $calculator = [[100, 0, 0, 0, 0], 100, 0, 0, 0];
        yield 'Accepted outstanding = Grand' => [PaymentStates::STATE_CAPTURED, $subject, $calculator];

        // 5) Paid = Deposit
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0);
        $calculator = [[100, 50, 0, 50, 0], 0, 0, 0, 0];
        yield 'Paid = Deposit' => [PaymentStates::STATE_DEPOSIT, $subject, $calculator];

        // 6) Expired > 0
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 50);
        $calculator = [[100, 0, 0, 0, 0], 0, 50, 0, 0];
        yield 'Expired > 0' => [PaymentStates::STATE_OUTSTANDING, $subject, $calculator];

        // 8) Paid + Pending = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0);
        $calculator = [[100, 50, 0, 0, 50], 0, 0, 0, 0];
        yield 'Paid + Pending = Grand' => [PaymentStates::STATE_PENDING, $subject, $calculator];

        // 7) Paid = Refunded = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0, true, InvoiceStates::STATE_PARTIAL, false);
        $calculator = [[0, 100, 100, 0, 0], 0, 0, 0, 0];
        yield 'Paid = Refunded = Grand (not fully invoiced)' => [PaymentStates::STATE_CAPTURED, $subject, $calculator];

        // 9) Paid = Refunded = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0, true, InvoiceStates::STATE_CREDITED, true);
        $calculator = [[0, 100, 100, 0, 0], 0, 0, 0, 0];
        yield 'Paid = Refunded = Grand (fully invoiced)' => [PaymentStates::STATE_REFUNDED, $subject, $calculator];

        // 10) Not paid, fully credited
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0, false, InvoiceStates::STATE_CREDITED, true);
        $calculator = [[0, 0, 0, 0, 0], 0, 0, 0, 0];
        yield 'Not paid, fully credited' => [PaymentStates::STATE_CANCELED, $subject, $calculator];

        // 11) Failed = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0);
        $calculator = [[100, 0, 0, 0, 0], 0, 0, 100, 0];
        yield 'Failed = Grand' => [PaymentStates::STATE_FAILED, $subject, $calculator];

        // 12) Canceled = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_EUR, 0, 0);
        $calculator = [[100, 0, 0, 0, 0], 0, 0, 0, 100];
        yield 'Canceled = Grand' => [PaymentStates::STATE_CANCELED, $subject, $calculator];

        // 13) USD Paid = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_USD, 0, 0, true, InvoiceStates::STATE_PARTIAL, false);
        $calculator = [[125, 125, 0, 0, 0], 0, 0, 0, 0];
        yield 'USD Paid = Grand (not fully invoiced)' => [PaymentStates::STATE_CAPTURED, $subject, $calculator];

        // 14) USD Paid = Grand
        $subject = $this->createSubject(Fixture::CURRENCY_USD, 0, 0, true, InvoiceStates::STATE_COMPLETED, true);
        $calculator = [[125, 125, 0, 0, 0], 0, 0, 0, 0];
        yield 'USD Paid = Grand (fully invoiced)' => [PaymentStates::STATE_COMPLETED, $subject, $calculator];
    }

    /**
     * @return SaleInterface|MockObject
     */
    private function createSubject(
        string $currency,
        float  $accepted = 0,
        float  $expired = 0,
        bool   $hasPayments = true,
        string $invoiceState = InvoiceStates::STATE_NEW,
        bool   $fullyInvoiced = false
    ): SaleInterface {
        $subject = $this->createMock(OrderInterface::class); // SaleInterface + InvoiceSubjectInterface

        $accepted = new Decimal((string)$accepted);
        $expired = new Decimal((string)$expired);

        $subject->method('getCurrency')->willReturn(Fixture::currency($currency));
        $subject->method('getOutstandingAccepted')->willReturn($accepted);
        $subject->method('getOutstandingExpired')->willReturn($expired);
        $subject->method('hasPayments')->willReturn($hasPayments);
        $subject->method('getInvoiceState')->willReturn($invoiceState);
        $subject->method('getPaymentState')->willReturn(PaymentStates::STATE_NEW);
        $subject->method('isFullyInvoiced')->willReturn($fullyInvoiced);

        return $subject;
    }

    private function configurePaymentCalculator(
        array $amounts,
        float $accepted = 0,
        float $expired = 0,
        float $failed = 0,
        float $canceled = 0
    ): void {
        $map = fn($v) => new Decimal((string)$v);

        $defaults = array_map($map, [0, 0, 0, 0, 0]);
        $amounts = array_map($map, $amounts);

        $accepted = new Decimal((string)$accepted);
        $expired = new Decimal((string)$expired);
        $failed = new Decimal((string)$failed);
        $canceled = new Decimal((string)$canceled);

        $this->paymentCalculator->method('getPaymentAmounts')->willReturn(array_replace($defaults, $amounts));
        $this->paymentCalculator->method('calculateOutstandingAcceptedTotal')->willReturn($accepted);
        $this->paymentCalculator->method('calculateOutstandingExpiredTotal')->willReturn($expired);
        $this->paymentCalculator->method('calculateFailedTotal')->willReturn($failed);
        $this->paymentCalculator->method('calculateCanceledTotal')->willReturn($canceled);
    }
}
