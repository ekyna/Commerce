<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Shipment\Resolver;

use Decimal\Decimal;
use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Shipment\Resolver\ShipmentSubjectStateResolver;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

use function array_map;
use function is_int;

/**
 * Class ShipmentSubjectStateResolverTest\Resolver
 * @package Ekyna\Component\Commerce\Tests\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectStateResolverTest extends TestCase
{
    private ShipmentSubjectCalculatorInterface|MockObject|null $calculator;
    private ?ShipmentSubjectStateResolver                      $resolver;

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
     * @dataProvider provideResolveState
     */
    public function testResolveState(string $expected, ?array $map, ShipmentSubjectInterface $subject = null): void
    {
        $subject = $subject ?: $this->createOrder();

        if (!is_null($map)) {
            $map = array_map(
                fn($row) => array_map(
                    fn($v) => is_int($v) ? new Decimal($v) : $v,
                    $row
                ),
                $map
            );

            $this
                ->calculator
                ->expects(self::once())
                ->method('buildShipmentQuantityMap')
                ->with($subject)
                ->willReturn($map);
        }

        $rc = new ReflectionClass(ShipmentSubjectStateResolver::class);
        $rm = $rc->getMethod('resolveState');

        self::assertEquals($expected, $rm->invoke($this->resolver, $subject));
    }

    public function provideResolveState(): Generator
    {
        yield 'Preparation case' => [
            ShipmentStates::STATE_PREPARATION,
            null,
            $this->createOrder([
                $this->createShipment(ShipmentStates::STATE_PREPARATION),
            ]),
        ];

        yield 'Pending (return) case' => [
            ShipmentStates::STATE_PENDING,
            null,
            $this->createOrder([
                $this->createShipment(ShipmentStates::STATE_SHIPPED),
            ], [
                $this->createShipment(ShipmentStates::STATE_PENDING, true),
            ]),
        ];

        yield 'New case 1' => [
            ShipmentStates::STATE_NEW,
            [],
        ];

        yield 'New case 2' => [
            ShipmentStates::STATE_NEW,
            [
                ['sold' => 10, 'shipped' => 0, 'returned' => 0, 'invoiced' => false],
            ],
        ];

        yield 'Canceled case 1' => [
            ShipmentStates::STATE_CANCELED,
            [
                ['sold' => 0, 'shipped' => 0, 'returned' => 0, 'invoiced' => true],
            ],
        ];

        yield 'Returned case 1' => [
            ShipmentStates::STATE_RETURNED,
            [
                ['sold' => 0, 'shipped' => 10, 'returned' => 10, 'invoiced' => true],
            ],
        ];

        yield 'Returned case 2' => [
            ShipmentStates::STATE_RETURNED,
            [
                ['sold' => 0, 'shipped' => 8, 'returned' => 8, 'invoiced' => true],
            ],
        ];

        yield 'Returned case 3' => [
            ShipmentStates::STATE_RETURNED,
            [
                ['sold' => 8, 'shipped' => 8, 'returned' => 8, 'invoiced' => false],
            ],
        ];

        yield 'Completed case 1' => [
            ShipmentStates::STATE_COMPLETED,
            [
                ['sold' => 10, 'shipped' => 10, 'returned' => 0, 'invoiced' => true],
            ],
        ];

        yield 'Completed case 2' => [
            ShipmentStates::STATE_COMPLETED,
            [
                ['sold' => 10, 'shipped' => 15, 'returned' => 5, 'invoiced' => true],
            ],
        ];

        yield 'Completed case 3' => [
            ShipmentStates::STATE_COMPLETED,
            [
                ['sold' => 8, 'shipped' => 8, 'returned' => 0, 'invoiced' => false],
            ],
        ];

        yield 'Completed case 4' => [
            ShipmentStates::STATE_COMPLETED,
            [
                ['sold' => 10, 'shipped' => 10, 'returned' => 2, 'invoiced' => false],
            ],
        ];

        yield 'Completed case 5' => [
            ShipmentStates::STATE_COMPLETED,
            [
                ['sold' => 1, 'shipped' => 2, 'returned' => 1, 'invoiced' => false],
            ],
            $this->createOrder(isSample: true),
        ];

        yield 'Partial case 1' => [
            ShipmentStates::STATE_PARTIAL,
            [
                ['sold' => 10, 'shipped' => 8, 'returned' => 0, 'invoiced' => true],
            ],
        ];

        yield 'Partial case 2' => [
            ShipmentStates::STATE_PARTIAL,
            [
                ['sold' => 8, 'shipped' => 4, 'returned' => 0, 'invoiced' => false],
            ],
        ];

        yield 'Partial case 3' => [
            ShipmentStates::STATE_PARTIAL,
            [
                ['sold' => 10, 'shipped' => 10, 'returned' => 2, 'invoiced' => true],
            ],
        ];

        yield 'Partial case 4' => [
            ShipmentStates::STATE_PARTIAL,
            [
                ['sold' => 10, 'shipped' => 9, 'returned' => 2, 'invoiced' => false],
            ],
        ];

        yield 'Partial case 5' => [
            ShipmentStates::STATE_PARTIAL,
            [
                ['sold' => 10, 'shipped' => 16, 'returned' => 4, 'invoiced' => true],
            ],
        ];
    }

    private function createOrder(
        array $shipments = [],
        array $returns = [],
        bool  $isSample = false
    ): OrderInterface {
        $order = $this->createMock(OrderInterface::class);

        $order
            ->method('getShipments')
            ->withConsecutive([true], [false])
            ->willReturnOnConsecutiveCalls(new ArrayCollection($shipments), new ArrayCollection($returns));

        $order
            ->method('isSample')
            ->willReturn($isSample);

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
