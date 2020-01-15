<?php

namespace Ekyna\Component\Commerce\Tests\Customer\Updater;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Updater\CustomerUpdater;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates as State;
use Ekyna\Component\Commerce\Tests\TestCase;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;

/**
 * Class CustomerUpdaterTest
 * @package Ekyna\Component\Commerce\Tests\Customer\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerUpdaterTest extends TestCase
{
    /**
     * @var CustomerUpdater
     */
    private $updater;

    /**
     * @var CustomerInterface
     */
    private $customer;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->updater = new CustomerUpdater($this->getPersistenceHelperMock());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->updater  = null;
        $this->customer = null;
    }

    /**
     * @param PaymentInterface $payment
     * @param bool             $changed
     * @param float            $credit
     * @param float            $outstanding
     *
     * @dataProvider provideHandlePaymentInsert
     */
    public function testHandlePaymentInsert(PaymentInterface $payment, bool $changed, float $credit, float $outstanding): void
    {
        $this->init($payment);
        $this->assertEquals($changed, $this->updater->handlePaymentInsert($payment));
        $this->assertEquals($credit, $this->customer->getCreditBalance());
        $this->assertEquals($outstanding, $this->customer->getOutstandingBalance());
    }

    public function provideHandlePaymentInsert(): \Generator
    {
        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock());
        yield 'Unhandled method' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_MANUAL));
        yield 'Manual method' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment not accepted' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment accepted' => [$payment, true, 50, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT))
            ->setRefund(true);
        yield 'Credit method, refund accepted' => [$payment, true, 150, -100];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment not accepted' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment accepted' => [$payment, true, 100, -150];
    }

    /**
     * @param PaymentInterface $payment
     * @param bool             $changed
     * @param float            $credit
     * @param float            $outstanding
     * @param array            $stateCs
     * @param array            $amountCs
     *
     * @dataProvider provideHandlePaymentUpdate
     */
    public function testHandlePaymentUpdate(
        PaymentInterface $payment,
        bool $changed,
        float $credit,
        float $outstanding,
        array $stateCs = null,
        array $amountCs = null
    ): void {
        $this->init($payment);

        if (is_array($stateCs) && is_array($amountCs)) {
            $this->getPersistenceHelperMock()->expects($this->at(0))->method('getChangeSet')->willReturn($stateCs);
            $this->getPersistenceHelperMock()->expects($this->at(1))->method('getChangeSet')->willReturn($amountCs);
        }

        $this->assertEquals($changed, $this->updater->handlePaymentUpdate($payment));
        $this->assertEquals($credit, $this->customer->getCreditBalance());
        $this->assertEquals($outstanding, $this->customer->getOutstandingBalance());
    }

    public function provideHandlePaymentUpdate(): \Generator
    {
        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock());
        yield 'Unhandled method' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_MANUAL));
        yield 'Manual method' => [$payment, false, 100, -100];

        // CREDIT

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment not accepted, nothing changed' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment state changed from accepted to canceled' =>
        [$payment, true, 150, -100, [State::STATE_CAPTURED, State::STATE_CANCELED], []];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment state changed from canceled to accepted' =>
        [$payment, true, 50, -100, [State::STATE_CANCELED, State::STATE_CAPTURED], []];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment amount changed 75 to 50' =>
        [$payment, true, 125, -100, [], [75, 50]];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment amount changed 25 to 50' =>
        [$payment, true, 75, -100, [], [25, 50]];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment state and amount changed' =>
        [$payment, true, 125, -100, [State::STATE_CAPTURED, State::STATE_CANCELED], [25, 50]];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT))->setRefund(true);
        yield 'Credit method, refund state changed from accepted to canceled' =>
        [$payment, true, 50, -100, [State::STATE_CAPTURED, State::STATE_CANCELED], []];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT))->setRefund(true);
        yield 'Credit method, refund state changed from canceled to accepted' =>
        [$payment, true, 150, -100, [State::STATE_CANCELED, State::STATE_CAPTURED], []];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT))->setRefund(true);
        yield 'Credit method, refund amount changed 75 to 50' =>
        [$payment, true, 75, -100, [], [75, 50]];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT))->setRefund(true);
        yield 'Credit method, refund amount changed 25 to 50' =>
        [$payment, true, 125, -100, [], [25, 50]];

        // OUTSTANDING

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment not accepted, nothing changed' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment state changed from accepted to canceled' =>
        [$payment, true, 100, -50, [State::STATE_CAPTURED, State::STATE_CANCELED], []];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment state changed from canceled to accepted' =>
        [$payment, true, 100, -150, [State::STATE_CANCELED, State::STATE_CAPTURED], []];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment amount changed 75 to 50' =>
        [$payment, true, 100, -75, [], [75, 50]];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CAPTURED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment amount changed 25 to 50' =>
        [$payment, true, 100, -125, [], [25, 50]];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment amount and state changed' =>
        [$payment, true, 100, -75, [State::STATE_CAPTURED, State::STATE_CANCELED], [25, 50]];
    }

    /**
     * @param PaymentInterface $payment
     * @param bool             $changed
     * @param float            $credit
     * @param float            $outstanding
     *
     * @dataProvider provideHandlePaymentDelete
     */
    public function testHandlePaymentDelete(PaymentInterface $payment, bool $changed, float $credit, float $outstanding): void
    {
        $this->init($payment);
        $this->assertEquals($changed, $this->updater->handlePaymentDelete($payment));
        $this->assertEquals($credit, $this->customer->getCreditBalance());
        $this->assertEquals($outstanding, $this->customer->getOutstandingBalance());
    }

    public function provideHandlePaymentDelete(): \Generator
    {
        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock());
        yield 'Unhandled method' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_MANUAL));
        yield 'Manual method' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment not accepted' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT));
        yield 'Credit method, payment accepted' => [$payment, true, 150, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_CREDIT))
            ->setRefund(true);
        yield 'Credit method, refund accepted' => [$payment, true, 50, -100];

        $payment = Fixtures::createPayment('EUR', 50, State::STATE_CANCELED)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment not accepted' => [$payment, false, 100, -100];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING));
        yield 'Outstanding method, payment accepted' => [$payment, true, 100, -50];

        $payment = Fixtures::createPayment('EUR', 50)
            ->setMethod($this->getPaymentMethodMock(self::PAYMENT_METHOD_OUTSTANDING))
            ->setRefund(true);
        yield 'Outstanding method, refund accepted' => [$payment, true, 100, -150];
    }

    /**
     * @param float $amount
     * @param bool  $relative
     * @param float $expected
     *
     * @dataProvider provideUpdateCreditBalance
     */
    public function testUpdateCreditBalance(float $amount, bool $relative, float $expected): void
    {
        $this->init();

        $this->updater->updateCreditBalance($this->customer, $amount, $relative);

        $this->assertEquals($expected, $this->customer->getCreditBalance());
    }

    public function provideUpdateCreditBalance(): array
    {
        return [
            'Absolute positive' => [50, false, 50],
            'Relative positive' => [50, true, 150],
            'Absolute negative' => [-50, false, -50],
            'Relative negative' => [-50, true, 50],
        ];
    }

    /**
     * @param float $amount
     * @param bool  $relative
     * @param float $expected
     *
     * @dataProvider provideUpdateOutstandingBalance
     */
    public function testUpdateOutstandingBalance(float $amount, bool $relative, float $expected): void
    {
        $customer = Fixtures::createCustomer();
        $customer->setOutstandingBalance(-100);

        $this->updater->updateOutstandingBalance($customer, $amount, $relative);

        $this->assertEquals($expected, $customer->getOutstandingBalance());
    }

    public function provideUpdateOutstandingBalance(): array
    {
        return [
            'Absolute positive' => [50, false, 50],
            'Relative positive' => [50, true, -50],
            'Absolute negative' => [-50, false, -50],
            'Relative negative' => [-50, true, -150],
        ];
    }

    private function init(PaymentInterface $payment = null): void
    {
        $this->customer = Fixtures::createCustomer();
        $this->customer
            ->setCreditBalance(100)
            ->setOutstandingBalance(-100);

        if ($payment) {
            $order = Fixtures::createOrder();
            $order
                ->setCustomer($this->customer)
                ->addPayment($payment);
        }
    }
}
