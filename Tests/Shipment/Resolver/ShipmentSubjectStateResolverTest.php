<?php

namespace Ekyna\Component\Commerce\Tests\Shipment\Resolver;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceStates;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentSubjectStateResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class ShipmentSubjectStateResolverTest\Resolver
 * @package Ekyna\Component\Commerce\Tests\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectStateResolverTest extends TestCase
{
    /**
     * @var ShipmentSubjectCalculatorInterface|MockObject
     */
    private $calculator;

    /**
     * @var ShipmentSubjectStateResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        $this->calculator = $this->createMock(ShipmentSubjectCalculatorInterface::class);
        $this->resolver = new ShipmentSubjectStateResolver($this->calculator);
    }

    protected function tearDown(): void
    {
        $this->resolver = null;
        $this->calculator = null;
    }

    /**
     * @param string                   $expected
     * @param ShipmentSubjectInterface $subject
     * @param array                    $map
     *
     * @dataProvider provide_resolveState
     */
    public function test_resolveState(string $expected, ShipmentSubjectInterface $subject, array $map = null): void
    {
        if (!is_null($map)) {
            $this
                ->calculator
                ->expects(self::once())
                ->method('buildShipmentQuantityMap')
                ->with($subject)
                ->willReturn($map);
        }

        $rc = new \ReflectionClass(ShipmentSubjectStateResolver::class);
        $rm = $rc->getMethod('resolveState');
        $rm->setAccessible(true);

        $this->assertEquals($expected, $rm->invoke($this->resolver, $subject));
    }

    public function provide_resolveState(): \Generator
    {
        yield 'Preparation case' => [
            ShipmentStates::STATE_PREPARATION,
            $this->createOrder(null, null, [
                $this->createShipment(ShipmentStates::STATE_PREPARATION),
            ]),
        ];

        yield 'Pending (return) case' => [
            ShipmentStates::STATE_PENDING,
            $this->createOrder(null, null, [
                $this->createShipment(ShipmentStates::STATE_SHIPPED),
            ], [
                $this->createShipment(ShipmentStates::STATE_PENDING, true),
            ]),
        ];

        yield 'New case' => [ShipmentStates::STATE_NEW, $this->createOrder(), []];

        yield 'Canceled case 1' => [
            ShipmentStates::STATE_CANCELED,
            $this->createOrder(),
            [
                ['sold' => 0, 'shipped' => 0, 'returned' => 0],
            ],
        ];

        yield 'Returned case 1' => [
            ShipmentStates::STATE_RETURNED,
            $this->createOrder(),
            [
                ['sold' => 0, 'shipped' => 10, 'returned' => 10],
            ],
        ];

        yield 'Returned case 2' => [
            ShipmentStates::STATE_RETURNED,
            $this->createOrder(),
            [
                ['sold' => 0, 'shipped' => 8, 'returned' => 8],
            ],
        ];

        yield 'Completed case 1' => [
            ShipmentStates::STATE_COMPLETED,
            $this->createOrder(),
            [
                ['sold' => 10, 'shipped' => 10, 'returned' => 0,],
            ],
        ];

        yield 'Completed case 2' => [
            ShipmentStates::STATE_COMPLETED,
            $this->createOrder(),
            [
                ['sold' => 8, 'shipped' => 8, 'returned' => 0,],
            ],
        ];

        yield 'Completed case 3' => [
            ShipmentStates::STATE_COMPLETED,
            $this->createOrder(),
            [
                ['sold' => 0, 'shipped' => 10, 'returned' => 0,],
            ],
        ];

        yield 'Partial case 1' => [
            ShipmentStates::STATE_PARTIAL,
            $this->createOrder(),
            [
                ['sold' => 10, 'shipped' => 8, 'returned' => 0,],
            ],
        ];

        yield 'Partial case 2' => [
            ShipmentStates::STATE_PARTIAL,
            $this->createOrder(),
            [
                ['sold' => 10, 'shipped' => 10, 'returned' => 2,],
            ],
        ];

        yield 'Canceled case 2' => [
            ShipmentStates::STATE_CANCELED,
            $this->createOrder(null, InvoiceStates::STATE_CREDITED),
            [
                ['sold' => 0, 'shipped' => 0, 'returned' => 0,],
            ],
        ];

        yield 'Canceled case 3' => [
            ShipmentStates::STATE_CANCELED,
            $this->createOrder(PaymentStates::STATE_CANCELED, null),
            [
                ['sold' => 0, 'shipped' => 0, 'returned' => 0,],
            ],
        ];

        yield 'Test' => [
            ShipmentStates::STATE_CANCELED,
            $this->createOrder(PaymentStates::STATE_CANCELED, null),
            [
                ['sold' => 0, 'shipped' => 0, 'returned' => 0,],
            ],
        ];
    }

    private function createOrder(
        string $paymentState = null,
        string $invoiceState = null,
        array $shipments = [],
        array $returns = [],
        bool $sample = false
    ): OrderInterface {
        $order = $this->createMock(OrderInterface::class);

        $order->method('getPaymentState')->willReturn($paymentState ?? PaymentStates::STATE_NEW);
        $order->method('getInvoiceState')->willReturn($invoiceState ?? InvoiceStates::STATE_NEW);

        $order
            ->method('getShipments')
            ->withConsecutive([true], [false])
            ->willReturnOnConsecutiveCalls($shipments, $returns);

        $order->method('isSample')->willReturn($sample);

        return $order;
    }

    private function createShipment(string $state, bool $return = false): ShipmentInterface
    {
        $shipment = $this->createMock(ShipmentInterface::class);
        $shipment->method('getState')->willReturn($state);
        $shipment->method('isReturn')->willReturn($return);

        return $shipment;
    }
}
