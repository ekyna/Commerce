<?php

namespace Ekyna\Component\Commerce\Tests\Customer\Updater;

use Ekyna\Component\Commerce\Customer\Updater\CustomerUpdater;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates as State;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;

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

        $this->updater = null;
    }

    /**
     * @param array $payment
     * @param array $expected
     *
     * @dataProvider provideHandlePaymentInsert
     */
    public function testHandlePaymentInsert(array $payment, array $expected): void
    {
        $customer = Fixture::customer([
            'credit_balance'      => 100,
            'outstanding_balance' => -100,
        ]);

        $payment = Fixture::payment(array_replace([
            'amount' => 50,
            'order'  => [
                'customer' => $customer,
            ],
        ], $payment));

        $this->assertEquals(!empty($expected), $this->updater->handlePaymentInsert($payment));

        $expected = array_replace(['credit' => 100, 'outstanding' => -100], $expected);

        $this->assertEquals($expected['credit'], $customer->getCreditBalance());
        $this->assertEquals($expected['outstanding'], $customer->getOutstandingBalance());
    }

    public function provideHandlePaymentInsert(): \Generator
    {
        yield 'Unhandled method' => [
            [],
            [],
        ];

        yield 'Manual method' => [
            ['method' => Fixture::PAYMENT_METHOD_MANUAL],
            [],
        ];

        yield 'Credit method, payment not accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT, 'state' => State::STATE_CANCELED],
            [],
        ];

        yield 'Credit method, payment accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 50,],
        ];

        yield 'Credit method, refund accepted' => [
            ['refund' => true, 'method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 150,],
        ];

        yield 'Outstanding method, payment not accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING, 'state' => State::STATE_CANCELED],
            [],
        ];

        yield 'Outstanding method, payment accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING],
            ['outstanding' => -150,],
        ];
    }

    /**
     * @param array $payment
     * @param array $expected
     * @param array $changed
     *
     * @dataProvider provideHandlePaymentUpdate
     */
    public function testHandlePaymentUpdate(array $payment, array $expected, array $changed = []): void
    {
        $customer = Fixture::customer([
            'credit_balance'      => 100,
            'outstanding_balance' => -100,
        ]);

        $payment = Fixture::payment(array_replace([
            'amount' => 50,
            'order'  => [
                'customer' => $customer,
            ],
        ], $payment));

        if ($changed) {
            $this
                ->getPersistenceHelperMock()
                ->expects($this->at(0))
                ->method('getChangeSet')
                ->willReturn(isset($changed['state']) ? [$changed['state'], $payment->getState()] : []);

            $this
                ->getPersistenceHelperMock()
                ->expects($this->at(1))
                ->method('getChangeSet')
                ->willReturn(isset($changed['amount']) ? [$changed['amount'], $payment->getAmount()] : []);
        }

        $this->assertEquals(!empty($expected), $this->updater->handlePaymentUpdate($payment));

        $expected = array_replace(['credit' => 100, 'outstanding' => -100], $expected);

        $this->assertEquals($expected['credit'], $customer->getCreditBalance());
        $this->assertEquals($expected['outstanding'], $customer->getOutstandingBalance());
    }

    public function provideHandlePaymentUpdate(): \Generator
    {
        yield 'Unhandled method' => [
            [],
            [],
        ];

        yield 'Manual method' => [
            ['method' => Fixture::PAYMENT_METHOD_MANUAL],
            [],
        ];

        // CREDIT

        yield 'Credit method, payment not accepted, nothing changed' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT, 'state' => State::STATE_CANCELED],
            [],
        ];

        yield 'Credit method, payment state changed from accepted to canceled' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT, 'state' => State::STATE_CANCELED],
            ['credit' => 150,],
            ['state' => State::STATE_CAPTURED],
        ];

        yield 'Credit method, payment state changed from canceled to accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 50,],
            ['state' => State::STATE_CANCELED],
        ];

        yield 'Credit method, payment amount changed 75 to 50' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 125,],
            ['amount' => 75],
        ];

        yield 'Credit method, payment amount changed 25 to 50' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 75,],
            ['amount' => 25],
        ];

        yield 'Credit method, payment state and amount changed' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT, 'state' => State::STATE_CANCELED],
            ['credit' => 125,],
            ['state' => State::STATE_CAPTURED, 'amount' => 25],
        ];

        yield 'Credit method, refund state changed from accepted to canceled' => [
            ['refund' => true, 'method' => Fixture::PAYMENT_METHOD_CREDIT, 'state' => State::STATE_CANCELED,],
            ['credit' => 50,],
            ['state' => State::STATE_CAPTURED],
        ];

        yield 'Credit method, refund state changed from canceled to accepted' => [
            ['refund' => true, 'method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 150,],
            ['state' => State::STATE_CANCELED],
        ];

        yield 'Credit method, refund amount changed 75 to 50' => [
            ['refund' => true, 'method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 75,],
            ['amount' => 75],
        ];

        yield 'Credit method, refund amount changed 25 to 50' => [
            ['refund' => true, 'method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 125,],
            ['amount' => 25],
        ];

        // OUTSTANDING

        yield 'Outstanding method, payment not accepted, nothing changed' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING, 'state' => State::STATE_CANCELED],
            [],
        ];

        yield 'Outstanding method, payment state changed from accepted to canceled' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING, 'state' => State::STATE_CANCELED],
            ['outstanding' => -50],
            ['state' => State::STATE_CAPTURED],
        ];

        yield 'Outstanding method, payment state changed from canceled to accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING],
            ['outstanding' => -150],
            ['state' => State::STATE_CANCELED],
        ];

        yield 'Outstanding method, payment amount changed 75 to 50' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING],
            ['outstanding' => -75],
            ['amount' => 75],
        ];

        yield 'Outstanding method, payment amount changed 25 to 50' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING],
            ['outstanding' => -125],
            ['amount' => 25],
        ];

        yield 'Outstanding method, payment amount and state changed' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING, 'state' => State::STATE_CANCELED],
            ['outstanding' => -75],
            ['state' => State::STATE_CAPTURED, 'amount' => 25],
        ];
    }

    /**
     * @param array $payment
     * @param array $expected
     *
     * @dataProvider provideHandlePaymentDelete
     */
    public function testHandlePaymentDelete(array $payment, array $expected): void
    {
        $customer = Fixture::customer([
            'credit_balance'      => 100,
            'outstanding_balance' => -100,
        ]);

        $payment = Fixture::payment(array_replace([
            'amount' => 50,
            'order'  => [
                'customer' => $customer,
            ],
        ], $payment));

        $this->assertEquals(!empty($expected), $this->updater->handlePaymentDelete($payment));

        $expected = array_replace(['credit' => 100, 'outstanding' => -100], $expected);

        $this->assertEquals($expected['credit'], $customer->getCreditBalance());
        $this->assertEquals($expected['outstanding'], $customer->getOutstandingBalance());
    }

    public function provideHandlePaymentDelete(): \Generator
    {
        yield 'Unhandled method' => [
            [],
            [],
        ];

        yield 'Manual method' => [
            ['method' => Fixture::PAYMENT_METHOD_MANUAL],
            [],
        ];

        yield 'Credit method, payment not accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT, 'state' => State::STATE_CANCELED],
            [],
        ];

        yield 'Credit method, payment accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 150,],
        ];

        yield 'Credit method, refund accepted' => [
            ['refund' => true, 'method' => Fixture::PAYMENT_METHOD_CREDIT],
            ['credit' => 50,],
        ];

        yield 'Outstanding method, payment not accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING, 'state' => State::STATE_CANCELED],
            [],
        ];

        yield 'Outstanding method, payment accepted' => [
            ['method' => Fixture::PAYMENT_METHOD_OUTSTANDING],
            ['outstanding' => -50,],
        ];

        yield 'Outstanding method, refund accepted' => [
            ['refund' => true, 'method' => Fixture::PAYMENT_METHOD_OUTSTANDING],
            ['outstanding' => -150,],
        ];
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
        $customer = Fixture::customer([
            'credit_balance' => 100,
        ]);

        $this->updater->updateCreditBalance($customer, $amount, $relative);

        $this->assertEquals($expected, $customer->getCreditBalance());
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
        $customer = Fixture::customer([
            'outstanding_balance' => -100,
        ]);

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

}
