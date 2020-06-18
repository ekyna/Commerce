<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Manager;

use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManager;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\Stock\StockTestCase;

/**
 * Class StockAssignmentManagerTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentManagerTest extends StockTestCase
{
    /**
     * @var StockAssignmentManager
     */
    private $manager;

    protected function setUp(): void
    {
        $this->manager = new StockAssignmentManager(
            $this->getPersistenceHelperMock(),
            $this->getStockAssignmentCacheMock(),
            $this->getSaleFactory()
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->manager = null;
    }

    /** @dataProvider provide_persist */
    public function test_persist(array $assignment, bool $expected): void
    {
        $assignment = Fixture::stockAssignment($assignment);

        $this->assertPersistence($assignment, [
            'remove'   => false,
            'order'    => $expected ? $this->once() : $this->never(),
            'schedule' => false,
        ]);

        $this->manager->persist($assignment);
    }

    public function provide_persist(): \Generator
    {
        yield 'Empty' => [
            [
                '_id'  => null,
            ],
            false,
        ];

        yield 'Not empty' => [
            [
                '_id'  => null,
                'sold' => 10.,
            ],
            true,
        ];
    }

    /** @dataProvider provide_remove */
    public function test_remove(array $assignment, array $result): void
    {
        $assignment = Fixture::stockAssignment($assignment);

        if ($result['exception']) {
            $this->expectException($result['exception']);
        }

        $this->assertPersistence($assignment, [
            'remove'   => true,
            'order'    => $result['removed'] ? $this->once() : $this->never(),
            'schedule' => false,
        ]);

        if ($result['cached']) {
            $this->assertCached($assignment, !$result['removed']);
        }

        $this->manager->remove($assignment, $result['hard']);

        if ($result['removed']) {
            $this->assertNull($assignment->getSaleItem());
            $this->assertNull($assignment->getStockUnit());
        }
    }

    public function provide_remove(): \Generator
    {
        // If sale is in a stockable state,
        // Any item MUST have at least one assignment.
        // Remove this assignment only if there is another (non empty ?)

        // If sale is not accepted
        //     -> remove if empty

        yield 'Non stockable sale' => [
            [
                '_id'  => null,
                'unit' => [
                    'item' => [
                        'order' => [],
                    ],
                ],
                'item' => [
                    'order' => [],
                ],
            ],
            [
                'hard'      => false,
                'exception' => null,
                'removed'   => true,
                'cached'    => false,
            ],
        ];

        yield 'Only one assignment' => [
            [
                '_id'  => null,
                'unit' => [
                    'item' => [
                        'order' => [],
                    ],
                ],
                'item' => [
                    'order' => ['state' => OrderStates::STATE_ACCEPTED],
                ],
            ],
            [
                'hard'      => false,
                'exception' => null,
                'removed'   => false,
                'cached'    => false,
            ],
        ];

        yield 'Multiple assignments' => [
            [
                '_id'  => null,
                'unit' => [
                    'item' => [
                        'order' => [],
                    ],
                ],
                'item' => [
                    'order'       => ['state' => OrderStates::STATE_ACCEPTED],
                    'assignments' => [[]],
                ],
            ],
            [
                'hard'      => false,
                'exception' => null,
                'removed'   => true,
                'cached'    => false,
            ],
        ];

        yield 'Multiple assignments, with ID' => [
            [
                '_id'  => true,
                'unit' => [
                    'item' => [
                        'order' => [],
                    ],
                ],
                'item' => [
                    'order'       => ['state' => OrderStates::STATE_ACCEPTED],
                    'assignments' => [[]],
                ],
            ],
            [
                'hard'      => false,
                'exception' => null,
                'removed'   => false,
                'cached'    => true,
            ],
        ];
    }

    protected function assertCached(StockAssignmentInterface $assignment, bool $isDone): void
    {
        $this
            ->getStockAssignmentCacheMock()
            ->expects($isDone ? $this->once() : $this->never())
            ->method('addRemoved')
            ->with($assignment);
    }

    public function test_create_withItem(): void
    {
        $item = Fixture::orderItem();

        // Without unit, it should not look for cached removed assignment
        $this
            ->getStockAssignmentCacheMock()
            ->expects($this->never())
            ->method('findRemoved');

        $assignment = $this->manager->create($item);

        // Method should return an assignment
        $this->assertInstanceOf(StockAssignmentInterface::class, $assignment);
        // Assignment should be initialized with the given sale item
        $this->assertSame($item, $assignment->getSaleItem());
        // Assignment should be empty
        $this->assertTrue($assignment->isEmpty());
    }

    public function test_create_withItemAndUnit(): void
    {
        $item = Fixture::orderItem();
        $unit = Fixture::stockUnit();

        // With unit, it should look for cached removed assignment
        $this
            ->getStockAssignmentCacheMock()
            ->expects($this->once())
            ->method('findRemoved')
            ->with($unit, $item)
            ->willReturn(null);

        $assignment = $this->manager->create($item, $unit);

        // Method should return an assignment
        $this->assertInstanceOf(StockAssignmentInterface::class, $assignment);
        // Assignment should be initialized with the given sale item
        $this->assertSame($item, $assignment->getSaleItem());
        // Assignment should be initialized with the given stock unit
        $this->assertSame($unit, $assignment->getStockUnit());
        // Assignment should be empty
        $this->assertTrue($assignment->isEmpty());
    }

    public function test_create_withItemAndCachedUnit(): void
    {
        $item = Fixture::orderItem();
        $unit = Fixture::stockUnit();

        // With unit, it should use the cached removed assignment
        $this
            ->getStockAssignmentCacheMock()
            ->expects($this->once())
            ->method('findRemoved')
            ->with($unit, $item)
            ->willReturn($assignment = Fixture::stockAssignment());

        // Method should return an assignment
        $this->assertSame($assignment, $this->manager->create($item, $unit));
        // Assignment should be initialized with the given sale item
        $this->assertSame($item, $assignment->getSaleItem());
        // Assignment should be initialized with the given stock unit
        $this->assertSame($unit, $assignment->getStockUnit());
        // Assignment should be empty
        $this->assertTrue($assignment->isEmpty());
    }

    public function test_onEventQueueClose(): void
    {
        // Given the stock assignment cache will return 2 assignments
        $this
            ->getStockAssignmentCacheMock()
            ->expects($this->once())
            ->method('flush')
            ->willReturn([
                $as1 = Fixture::stockAssignment(),
                $as2 = Fixture::stockAssignment(),
            ]);

        // Then the stock assignment cache will remove these assignments without scheduling event
        $this
            ->getPersistenceHelperMock()
            ->expects($this->exactly(2))
            ->method('remove')
            ->withConsecutive(
                [$as1, false],
                [$as2, false],
            );

        $this->manager->onEventQueueClose();
    }
}
