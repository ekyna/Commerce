<?php

namespace Ekyna\Component\Commerce\Tests\Invoice\Resolver;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Invoice\Resolver\InvoiceSubjectStateResolver;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class InvoiceSubjectStateResolverTest
 * @package Ekyna\Component\Commerce\Tests\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSubjectStateResolverTest extends TestCase
{
    /**
     * @var InvoiceSubjectCalculatorInterface|MockObject
     */
    private $calculator;

    /**
     * @var InvoiceSubjectStateResolver
     */
    private $resolver;


    protected function setUp(): void
    {
        $this->calculator = $this->createMock(InvoiceSubjectCalculatorInterface::class);
        $this->resolver = new InvoiceSubjectStateResolver($this->calculator);
    }

    protected function tearDown(): void
    {
        $this->resolver = null;
        $this->calculator = null;
    }


    /**
     * @param string                   $expected
     * @param InvoiceSubjectInterface $subject
     * @param array                    $map
     *
     * @dataProvider provide_resolveState
     */
    public function test_resolveState(string $expected, InvoiceSubjectInterface $subject, array $map): void
    {
        $map = array_map(function($item) {
            return array_replace([
                'total'    => 0,
                'invoiced' => 0,
                'adjusted' => 0,
                'credited' => 0,
                'shipped'  => 0,
                'returned' => 0,
            ], $item);
        }, $map);

        $this
            ->calculator
            ->expects(self::once())
            ->method('buildInvoiceQuantityMap')
            ->with($subject)
            ->willReturn($map);

        $rc = new \ReflectionClass(InvoiceSubjectStateResolver::class);
        $rm = $rc->getMethod('resolveState');
        $rm->setAccessible(true);

        $this->assertEquals($expected, $rm->invoke($this->resolver, $subject));
    }

    public function provide_resolveState(): \Generator
    {
        yield 'New case 1' => [InvoiceStates::STATE_NEW, $this->createOrder(), []];

        yield 'Credited case 1' => [
            InvoiceStates::STATE_CREDITED,
            $this->createOrder(),
            [
                ['total' => 10, 'invoiced' => 10, 'credited' => 10],
            ],
        ];

        yield 'Completed case 1' => [
            InvoiceStates::STATE_COMPLETED,
            $this->createOrder(),
            [
                ['total' => 10, 'invoiced' => 10, 'credited' => 0],
            ],
        ];

        yield 'Partial case 1' => [
            InvoiceStates::STATE_PARTIAL,
            $this->createOrder(),
            [
                ['total' => 10, 'invoiced' => 8, 'credited' => 0],
            ],
        ];

        yield 'Canceled case 3' => [
            InvoiceStates::STATE_CANCELED,
            $this->createOrder(PaymentStates::STATE_CANCELED),
            [
                ['total' => 10, 'invoiced' => 0, 'credited' => 0],
            ],
        ];

        yield 'New case 2' => [
            InvoiceStates::STATE_NEW,
            $this->createOrder(),
            [
                ['total' => 10, 'invoiced' => 0, 'credited' => 0],
            ],
        ];
    }

    private function createOrder(string $paymentState = null): OrderInterface
    {
        $order = $this->createMock(OrderInterface::class);

        $order->method('getPaymentState')->willReturn($paymentState ?? PaymentStates::STATE_NEW);

        return $order;
    }
}
