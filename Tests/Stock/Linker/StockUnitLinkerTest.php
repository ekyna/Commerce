<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Linker;

use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Order\Entity\OrderItemStockAssignment;
use Ekyna\Component\Commerce\Stock\Linker\StockUnitLinker;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use Ekyna\Component\Commerce\Tests\Stock\StockTestCase;

/**
 * Class StockUnitLinkerTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitLinkerTest extends StockTestCase
{
    /**
     * @var StockUnitLinker
     */
    private $linker;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->linker = new StockUnitLinker(
            $this->getPersistenceHelperMock(),
            $this->getStockUnitUpdaterMock(),
            $this->getStockUnitResolverMock(),
            $this->getCurrencyConverter()
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->linker = null;
    }

    /**
     * Supplier order item      New stock unit
     *  |----------------|    |----------------|
     *  |                |    | Ordered     20 |
     *  |       20       |--->| Sold         0 |
     *  |                |    |                |
     *  |----------------|    |----------------|
     */
    public function test_linkItem(): void
    {
        // Given the subject is ordered for 20 quantity with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 20, 12)
            ->setOrder(Fixtures::createSupplierOrder(null, null, 'now'));

        // Given the stock unit resolver will not find a linkable stock unit
        $this->getStockUnitResolverMock()
            ->expects($this->once())
            ->method('findLinkable')
            ->with($supplierItem)
            ->willReturn(null);

        // Given the stock unit resolver will create a new stock unit
        $newStockUnit = new StockUnit();
        $this->getStockUnitResolverMock()
            ->expects($this->once())
            ->method('createBySubjectRelative')
            ->with($supplierItem)
            ->willReturn($newStockUnit);

        // Then the stock unit updated should be called
        $this->getStockUnitUpdaterMock()
            ->expects($this->once())
            ->method('updateOrdered')
            ->willReturnCallback(function(StockUnitInterface $unit) {
                $unit->setOrderedQuantity(20);
                Fixtures::resolveStockUnitState($unit);
            })
            ->with($newStockUnit, 20, false);

        // Test
        $this->linker->linkItem($supplierItem);

        // Then the supplier order item should be linked to a stock unit
        $this->assertNotNull($supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $newStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $newStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $newStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $newStockUnit->getState());
        // Then the stock unit should not have assignment
        $this->assertEmpty($newStockUnit->getStockAssignments());
    }

    /**
     * Supplier order item    Existing stock unit
     *  |----------------|    |----------------|
     *  |                |    | Ordered     20 |
     *  |                |    | Sold        10 |
     *  |                |    |                |
     *  |       20       |--->| Assignments    |
     *  |                |    | |------------| |
     *  |                |    | |     10     | |
     *  |                |    | |------------| |
     *  |----------------|    |----------------|
     */
    public function test_linkItem_whileNoOverflow(): void
    {
        // Given the subject is ordered for 20 quantity with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 20, 12)
            ->setOrder(Fixtures::createSupplierOrder(null, null, 'now'));

        // Given the subject has been sold for 10 quantity
        $orderItem = Fixtures::createOrderItem(10);
        $stockUnit = Fixtures::createStockUnit()->setSoldQuantity(10);
        Fixtures::createStockAssignment($stockUnit, $orderItem, 10);

        // Given the stock unit resolver will find the available stock unit
        $this->getStockUnitResolverMock()
            ->method('findLinkable')
            ->with($supplierItem)
            ->willReturn($stockUnit);

        // Then the stock unit updated should be called
        $this->getStockUnitUpdaterMock()
            ->expects($this->once())
            ->method('updateOrdered')
            ->willReturnCallback(function(StockUnitInterface $unit) {
                $unit->setOrderedQuantity(20);
                Fixtures::resolveStockUnitState($unit);
            })
            ->with($stockUnit, 20, false);

        // Test
        $this->linker->linkItem($supplierItem);

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($stockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 10
        $this->assertEquals(10, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($stockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item
        $this->assertEquals($orderItem, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 10
        $this->assertEquals(10, $assignments[0]->getSoldQuantity());
    }

    /**
     * Supplier order item    Existing stock unit     New stock unit
     *  |----------------|    |----------------|    |----------------|
     *  |                |    | Ordered (0) 20 |    | Ordered      0 |
     *  |                |    | Sold   (30) 20 |    | Sold    (0) 10 |
     *  |                |    |                |    |                |
     *  |                |    | Assignments    |    | Assignments    |
     *  |                |    | |------------| |    |                |
     *  |       20       |--->| |     20     | |    |                |
     *  |                |    | |------------| |    |                |
     *  |                |    |                |    |                |
     *  |                |    | .............. |    | |------------| |
     *  |                |    | .     10     .------->|     10     | |
     *  |                |    | .............. |    | |------------| |
     *  |----------------|    |----------------|    |----------------|
     */
    public function test_linkItem_byMovingAssignment(): void
    {
        // Given the subject is ordered for 20 quantity with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 20, 12)
            ->setOrder(Fixtures::createSupplierOrder(null, null, 'now'));

        // Given the subject has been sold for 20 quantity and 10 quantity
        $orderItemA = Fixtures::createOrderItem(20)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        $orderItemB = Fixtures::createOrderItem(10)->setOrder(
            Fixtures::createOrder('EUR', '-1 day')
        );
        $linkableStockUnit = Fixtures::createStockUnit()->setSoldQuantity(30);
        Fixtures::createStockAssignment($linkableStockUnit, $orderItemA, 20);
        Fixtures::createStockAssignment($linkableStockUnit, $orderItemB, 10);


        // Given the stock unit resolver will find the available stock unit
        $this->getStockUnitResolverMock()
            ->method('findLinkable')
            ->with($supplierItem)
            ->willReturn($linkableStockUnit);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getStockUnitResolverMock()
            ->method('createBySubjectRelative')
            ->with($supplierItem)
            ->willReturn($newStockUnit);

        // Then the stock unit updated should be called with
        $this->getStockUnitUpdaterMock()
            ->expects($this->once())
            ->method('updateOrdered')
            ->willReturnCallback(function(StockUnitInterface $unit) {
                $unit->setOrderedQuantity(20);
                Fixtures::resolveStockUnitState($unit);
            })
            ->with($linkableStockUnit, 20, false);

        // Test
        $this->linker->linkItem($supplierItem);

        // Order item B and his assignment should be moved from the new stock unit to the linkable stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkableStockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $linkableStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $linkableStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $linkableStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkableStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkableStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkableStockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 20
        $this->assertEquals(20, $assignments[0]->getSoldQuantity());

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 10
        $this->assertEquals(10, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($newStockUnit->getStockAssignments()->toArray());
        // Then the new stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[0]->getOrderItem());
        // Then the new assignment's sold quantity should equal 10
        $this->assertEquals(10, $assignments[0]->getSoldQuantity());
    }

    /**
     * Supplier order item    Existing stock unit     New stock unit
     *  |----------------|    |----------------|    |----------------|
     *  |                |    | Ordered (0) 20 |    | Ordered      0 |
     *  |                |    | Sold   (30) 20 |    | Sold    (0) 10 |
     *  |                |    |                |    |                |
     *  |                |    | Assignments    |    | Assignments    |
     *  |                |    | |------------| |    |                |
     *  |       20       |--->| |     15     | |    |                |
     *  |                |    | |------------| |    |                |
     *  |                |    |                |    |                |
     *  |                |    | |------------| |    | |------------| |
     *  |                |    | |   (15) 5   |------->|     10     | |
     *  |                |    | |------------| |    | |------------| |
     *  |----------------|    |----------------|    |----------------|
     */
    public function test_linkItem_bySplittingAssignment(): void
    {
        // Given the subject is ordered for 20 quantity with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 20, 12)
            ->setOrder(Fixtures::createSupplierOrder(null, null, 'now'));

        // Given the subject has been sold for 15 quantity and 15 quantity
        $orderItemA = Fixtures::createOrderItem(15)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        $orderItemB = Fixtures::createOrderItem(15)->setOrder(
            Fixtures::createOrder('EUR', '-1 day')
        );
        $linkableStockUnit = Fixtures::createStockUnit()->setSoldQuantity(30);
        Fixtures::createStockAssignment($linkableStockUnit, $orderItemA, 15);
        Fixtures::createStockAssignment($linkableStockUnit, $orderItemB, 15);

        // Given the stock unit resolver will find the available stock unit
        $this->getStockUnitResolverMock()
            ->method('findLinkable')
            ->with($supplierItem)
            ->willReturn($linkableStockUnit);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getStockUnitResolverMock()
            ->method('createBySubjectRelative')
            ->with($supplierItem)
            ->willReturn($newStockUnit);

        // Tests
        $this->linker->linkItem($supplierItem);

        // Order item B's assignment should be split into the new stock unit for 10 quantity

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkableStockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $linkableStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $linkableStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $linkableStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkableStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkableStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkableStockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have two assignment
        $this->assertCount(2, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the first assignment's sold quantity should equal 15
        $this->assertEquals(15, $assignments[0]->getSoldQuantity());
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[1]->getOrderItem());
        // Then the second assignment's sold quantity should equal 5
        $this->assertEquals(5, $assignments[1]->getSoldQuantity());

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 10
        $this->assertEquals(10, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($newStockUnit->getStockAssignments()->toArray());
        // Then the new stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[0]->getOrderItem());
        // Then the new assignment's sold quantity should equal 5
        $this->assertEquals(10, $assignments[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit
     *  |-----------------|    |-----------------|
     *  |                 |    | Ordered (20) 25 |
     *  |                 |    | Sold         20 |
     *  |                 |    |                 |
     *  |     (20) 25     |----| Assignments     |
     *  |                 |    | |-------------| |
     *  |                 |    | |     20      | |
     *  |                 |    | |-------------| |
     *  |-----------------|    |-----------------|
     */
    public function test_applyItem_withPositiveChange(): void
    {
        // Given the subject is ordered for 25 quantity (20 before update) with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 25, 12);

        // Given the subject has been ordered for 20 quantity
        $linkedStockUnit = Fixtures::createStockUnit()
            ->setSupplierOrderItem($supplierItem)
            ->setSoldQuantity(20)
            ->setOrderedQuantity(20)
            ->setNetPrice(12)
            ->setState(StockUnitStates::STATE_PENDING);

        $orderItemA = Fixtures::createOrderItem(20)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemA, 20);

        // Given the supplier order item's quantity has changed from 20 to 25 (+8)
        $this->getPersistenceHelperMock()
            ->method('isChanged')
            ->with($supplierItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelperMock()
            ->method('getChangeSet')
            ->with($supplierItem, 'quantity')
            ->willReturn([20, 25]);

        // TODO test price change

        // Test
        $this->linker->applyItem($supplierItem);

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $linkedStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 25
        $this->assertEquals(25, $linkedStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 0
        $this->assertEquals(20, $linkedStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkedStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkedStockUnit->getState());
        // Then the stock unit should have no assignment
        $this->assertCount(1, $linkedStockUnit->getStockAssignments());
    }

    /**
     *  Supplier order item     Linked stock unit       New stock unit
     *  |-----------------|    |-----------------|    ...................
     *  |                 |    | Ordered (20) 30 |    . Ordered       0 .
     *  |                 |    | Sold    (20) 30 |    . Sold     (10) 0 .
     *  |                 |    |                 |    .                 .
     *  |                 |    | Assignments     |    . Assignments     .
     *  |                 |    | |-------------| |    .                 .
     *  |    (20) 30      |----| |     20      | |    .                 .
     *  |                 |    | |-------------| |    .                 .
     *  |                 |    |                 |    .                 .
     *  |                 |    | |-------------| |    . ............... .
     *  |                 |    | |     10      |<-------.     10      . .
     *  |                 |    | |-------------| |    . ............... .
     *  |-----------------|    |-----------------|    ...................
     */
    public function test_applyItem_withPositiveChange_byMovingAssignment(): void
    {
        // Given the subject is ordered for 30 quantity (20 before change) with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 30, 12);

        // Given the subject has been sold for 30 quantity
        $orderItemA = Fixtures::createOrderItem(20)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        $orderItemB = Fixtures::createOrderItem(10)->setOrder(
            Fixtures::createOrder('EUR', '-1 day')
        );
        $linkedStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(20)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierItem);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemA, 20);

        $newStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(0)
            ->setSoldQuantity(10);
        Fixtures::createStockAssignment($newStockUnit, $orderItemB, 10);

        // Given the supplier order item's quantity has changed from 20 to 30
        $this->getPersistenceHelperMock()
            ->method('isChanged')
            ->with($supplierItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelperMock()
            ->method('getChangeSet')
            ->with($supplierItem, 'quantity')
            ->willReturn([20, 30]);

        // Given the stock unit resolver will return a new stock unit
        $this->getStockUnitResolverMock()
            ->method('findLinkable')
            ->with($supplierItem)
            ->willReturn($newStockUnit);

        // Assert that stock unit B will be removed
        $this->getPersistenceHelperMock()
            ->expects($this->once())
            ->method('remove')
            ->with($newStockUnit, true);

        // Test
        $this->linker->applyItem($supplierItem);

        // Order item B ans his assignment should be moved from the new stock unit into the linked stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $linkedStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(30, $linkedStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(30, $linkedStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkedStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkedStockUnit->getState());
        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkedStockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have one assignment
        $this->assertCount(2, $assignments);
        // Then the first assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the first assignment's sold quantity should equal 20
        $this->assertEquals(20, $assignments[0]->getSoldQuantity());
        // Then the second assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[1]->getOrderItem());
        // Then the second assignment's sold quantity should equal 10
        $this->assertEquals(10, $assignments[1]->getSoldQuantity());

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 10
        $this->assertEquals(0, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());
        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($newStockUnit->getStockAssignments()->toArray());
        // Then the new stock unit should have one assignment
        $this->assertCount(0, $assignments);
    }

    /**
     *  Supplier order item     Linked stock unit       New stock unit
     *  |-----------------|    |-----------------|    |----------------|
     *  |                 |    | Ordered (20) 30 |    | Ordered      0 |
     *  |                 |    | Sold    (20) 30 |    | Sold   (20) 10 |
     *  |                 |    |                 |    |                |
     *  |                 |    | Assignments     |    | Assignments    |
     *  |                 |    | |-------------| |    |                |
     *  |     (20) 30     |----| |     15      | |    |                |
     *  |                 |    | |-------------| |    |                |
     *  |                 |    |                 |    |                |
     *  |                 |    | |-------------| |    | |------------| |
     *  |                 |    | |   (5) 15    |<-------|  (20) 10   | |
     *  |                 |    | |-------------| |    | |------------| |
     *  |-----------------|    |-----------------|    |----------------|
     */
    public function test_applyItem_withPositiveChange_bySplittingAssignment(): void
    {
        // Given the subject is ordered for 30 quantity (20 before change) with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 30, 12);

        // Given the subject has been sold for 40 quantity
        $orderItemA = Fixtures::createOrderItem(15)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        $orderItemB = Fixtures::createOrderItem(25)->setOrder(
            Fixtures::createOrder('EUR', '-1 day')
        );
        $linkedStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(20)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierItem);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemA, 15);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemB, 5);

        $newStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(0)
            ->setSoldQuantity(20);
        Fixtures::createStockAssignment($newStockUnit, $orderItemB, 20);

        // Given the supplier order item's quantity has changed from 20 to 30
        $this->getPersistenceHelperMock()
            ->method('isChanged')
            ->with($supplierItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelperMock()
            ->method('getChangeSet')
            ->with($supplierItem, 'quantity')
            ->willReturn([20, 30]);

        // Given the stock unit resolver will return a new stock unit
        $this->getStockUnitResolverMock()
            ->method('findLinkable')
            ->with($supplierItem)
            ->willReturn($newStockUnit);

        // Test
        $this->linker->applyItem($supplierItem);

        // Order item B ans his assignment should be split from the new stock unit into the linked stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $linkedStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 30
        $this->assertEquals(30, $linkedStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 30
        $this->assertEquals(30, $linkedStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkedStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkedStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkedStockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have one assignment
        $this->assertCount(2, $assignments);
        // Then the first assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the first assignment's sold quantity should equal 15
        $this->assertEquals(15, $assignments[0]->getSoldQuantity());
        // Then the second assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[1]->getOrderItem());
        // Then the second assignment's sold quantity should equal 15
        $this->assertEquals(15, $assignments[1]->getSoldQuantity());

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 10
        $this->assertEquals(10, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($newStockUnit->getStockAssignments()->toArray());
        // Then the new stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the second assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[0]->getOrderItem());
        // Then the second assignment's sold quantity should equal 10
        $this->assertEquals(10, $assignments[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit
     *  |-----------------|    |-----------------|
     *  |                 |    | Ordered (25) 20 |
     *  |                 |    | Sold         20 |
     *  |                 |    |                 |
     *  |     (25) 20     |----| Assignments     |
     *  |                 |    | |-------------| |
     *  |                 |    | |     20      | |
     *  |                 |    | |-------------| |
     *  |-----------------|    |-----------------|
     */
    public function test_applyItem_withNegativeChange(): void
    {
        // Given the subject is ordered for 20 quantity (25 before update) with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 20, 12);

        // Given the subject has been ordered for 20 quantity
        $orderItemA = Fixtures::createOrderItem(20)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );

        $linkedStockUnit = Fixtures::createStockUnit()
            ->setSupplierOrderItem($supplierItem)
            ->setOrderedQuantity(25)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setState(StockUnitStates::STATE_PENDING);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemA, 20);

        // Given the supplier order item's quantity has changed from 20 to 18 (-2)
        $this->getPersistenceHelperMock()
            ->method('isChanged')
            ->with($supplierItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelperMock()
            ->method('getChangeSet')
            ->with($supplierItem, 'quantity')
            ->willReturn([25, 20]);

        // Test
        $this->linker->applyItem($supplierItem);

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $linkedStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $linkedStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 0
        $this->assertEquals(20, $linkedStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkedStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkedStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkedStockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have no assignment
        $this->assertCount(1, $assignments);
        // Then the first assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the first assignment's sold quantity should equal 20
        $this->assertEquals(20, $assignments[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit       New stock unit
     *  |-----------------|    |-----------------|    |----------------|
     *  |                 |    | Ordered (30) 20 |    | Ordered      0 |
     *  |                 |    | Sold    (30) 20 |    | Sold    (0) 10 |
     *  |                 |    |                 |    |                |
     *  |                 |    | Assignments     |    | Assignments    |
     *  |                 |    | |-------------| |    |                |
     *  |     (30) 20     |----| |     20      | |    |                |
     *  |                 |    | |-------------| |    |                |
     *  |                 |    |                 |    |                |
     *  |                 |    | ............... |    | |------------| |
     *  |                 |    | .     10      .------->|     10     | |
     *  |                 |    | ............... |    | |------------| |
     *  |-----------------|    |-----------------|    |----------------|
     */
    public function test_applyItem_withNegativeChange_byMovingAssignment(): void
    {
        // Given the subject is ordered for 20 quantity (30 before change) with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(20, 12);

        // Given the subject has been sold for 30
        $orderItemA = Fixtures::createOrderItem(20)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        $orderItemB = Fixtures::createOrderItem(10)->setOrder(
            Fixtures::createOrder('EUR', '-1 day')
        );
        $linkedStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(30)
            ->setSoldQuantity(30)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierItem);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemA, 20);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemB, 10);


        // Given the supplier order item's quantity has changed from 30 to 20
        $this->getPersistenceHelperMock()
            ->method('isChanged')
            ->with($supplierItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelperMock()
            ->method('getChangeSet')
            ->with($supplierItem, 'quantity')
            ->willReturn([30, 20]);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getStockUnitResolverMock()
            ->method('findPendingOrReady')
            ->with($supplierItem)
            ->willReturn([]);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getStockUnitResolverMock()
            ->method('createBySubjectRelative')
            ->with($supplierItem)
            ->willReturn($newStockUnit);

        // Test
        $this->linker->applyItem($supplierItem);

        // Order item B ans his assignment should be moved from the linked stock unit into the new stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $linkedStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $linkedStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $linkedStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkedStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkedStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkedStockUnit->getStockAssignments()->toArray());
        // Then the linked stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 20
        $this->assertEquals(20, $assignments[0]->getSoldQuantity());

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 10
        $this->assertEquals(10, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());
        // Then the new stock unit should have one assignment
        $this->assertCount(1, $newStockUnit->getStockAssignments());
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $newStockUnit->getStockAssignments()[0]->getOrderItem());
        // Then the new assignment's sold quantity should equal 10
        $this->assertEquals(10, $newStockUnit->getStockAssignments()[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit       New stock unit
     *  |-----------------|    |-----------------|    |----------------|
     *  |                 |    | Ordered (30) 20 |    | Ordered      0 |
     *  |                 |    | Sold    (30) 20 |    | Sold    (0) 10 |
     *  |                 |    |                 |    |                |
     *  |                 |    | Assignments     |    | Assignments    |
     *  |                 |    | |-------------| |    |                |
     *  |     (30) 20     |----| |     15      | |    |                |
     *  |                 |    | |-------------| |    |                |
     *  |                 |    |                 |    |                |
     *  |                 |    | |-------------| |    | |------------| |
     *  |                 |    | |   (15) 5    |------->|     10     | |
     *  |                 |    | |-------------| |    | |------------| |
     *  |-----------------|    |-----------------|    |----------------|
     */
    public function test_applyItem_withNegativeChange_bySplittingAssignment(): void
    {
        // Given the subject is ordered for 20 quantity (30 before change) with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(20, 12);

        // Given the subject has been sold for 30
        $orderItemA = Fixtures::createOrderItem(15)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        $orderItemB = Fixtures::createOrderItem(15)->setOrder(
            Fixtures::createOrder('EUR', '-1 day')
        );
        $linkedStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(30)
            ->setSoldQuantity(30)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierItem);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemA, 15);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemB, 15);

        // Given the supplier order item's quantity has changed from 30 to 20
        $this->getPersistenceHelperMock()
            ->method('isChanged')
            ->with($supplierItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelperMock()
            ->method('getChangeSet')
            ->with($supplierItem, 'quantity')
            ->willReturn([30, 20]);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getStockUnitResolverMock()
            ->method('findPendingOrReady')
            ->with($supplierItem)
            ->willReturn([]);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getStockUnitResolverMock()
            ->method('createBySubjectRelative')
            ->with($supplierItem)
            ->willReturn($newStockUnit);

        // Test
        $this->linker->applyItem($supplierItem);

        // Order item B ans his assignment should be split from the linked stock unit into the new stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItem, $linkedStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $linkedStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $linkedStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkedStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkedStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkedStockUnit->getStockAssignments()->toArray());
        // Then the linked stock unit should have one assignment
        $this->assertCount(2, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 15
        $this->assertEquals(15, $assignments[0]->getSoldQuantity());
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[1]->getOrderItem());
        // Then the assignment's sold quantity should equal 5
        $this->assertEquals(5, $assignments[1]->getSoldQuantity());

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 10
        $this->assertEquals(10, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());
        // Then the new stock unit should have one assignment
        $this->assertCount(1, $newStockUnit->getStockAssignments());
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $newStockUnit->getStockAssignments()[0]->getOrderItem());
        // Then the new assignment's sold quantity should equal 10
        $this->assertEquals(10, $newStockUnit->getStockAssignments()[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit       New stock unit
     *  |-----------------|    |-----------------|     |----------------|
     *  |                 |    | Ordered (30) 20 |     | Ordered      0 |
     *  |                 |    | Sold    (30) 20 |     | Sold     (0) 5 |
     *  |                 |    |                 |     |                |
     *  |                 |    | Assignments     |     | Assignments    |
     *  |                 |    | |-------------| |     |                |
     *  |     (30) 20     |----| |   15 [A]    | |     |                |
     *  |                 |    | |-------------| |     |                |
     *  |                 |    |                 |     |                |
     *  |                 |    | |-------------| |     | |------------| |
     *  |                 |    | | (15) 5 [B]  |-------->|     5      | |
     *  |                 |    | |-------------| |  |  | |------------| |
     *  |-----------------|    |-----------------|  |  |----------------|
     *                                              |
     *                         Pending stock unit   |
     *                         |----------------|   |
     *                         | Ordered     15 |   |
     *                         | Sold   (10) 15 |   |
     *                         |                |   |
     *                         | Assignments    |   |
     *                         | |------------| |   |
     *                         | |   10 (15)  |<----|
     *                         | |------------| |
     *                         |----------------|
     */
    public function test_applyItem_withNegativeChange_byMovingAndSplittingAssignment(): void
    {
        // Given the subject is ordered for 20 quantity (30 before change) with a cost of 12 euros
        $supplierItemA = Fixtures::createSupplierOrderItem(null, 20, 12);

        // Given the subject has been sold for 30
        $orderItemA = Fixtures::createOrderItem(15)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        $orderItemB = Fixtures::createOrderItem(15)->setOrder(
            Fixtures::createOrder('EUR', '-1 day')
        );
        $linkedStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(30)
            ->setSoldQuantity(30)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierItemA);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemA, 15);
        Fixtures::createStockAssignment($linkedStockUnit, $orderItemB, 15);

        // Given the supplier order item's quantity has changed from 30 to 20
        $this->getPersistenceHelperMock()
            ->method('isChanged')
            ->with($supplierItemA, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelperMock()
            ->method('getChangeSet')
            ->with($supplierItemA, 'quantity')
            ->willReturn([30, 20]);

        // Given the stock unit resolver will return a pending stock unit
        $supplierItemB = Fixtures::createSupplierOrderItem(null, 15, 14)
            ->setOrder(Fixtures::createSupplierOrder(null, null, 'now'));
        $pendingStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(15)
            ->setSoldQuantity(10)
            ->setNetPrice(14)
            ->setSupplierOrderItem($supplierItemB);
        Fixtures::createStockAssignment($pendingStockUnit, $orderItemB, 10);

        $this->getStockUnitResolverMock()
            ->method('findPendingOrReady')
            ->with($supplierItemA)
            ->willReturn([$pendingStockUnit]);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getStockUnitResolverMock()
            ->method('createBySubjectRelative')
            ->with($supplierItemA)
            ->willReturn($newStockUnit);

        // Test
        $this->linker->applyItem($supplierItemA);

        // Order item B ans his assignment should be
        // split from the linked stock unit into the pending stock unit for 5 quantity and
        // split from the linked stock unit into the new stock unit for 5 quantity

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierItemA->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierItemA, $linkedStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $linkedStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $linkedStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkedStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkedStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkedStockUnit->getStockAssignments()->toArray());
        // Then the linked stock unit should have one assignment
        $this->assertCount(2, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 15
        $this->assertEquals(15, $assignments[0]->getSoldQuantity());
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[1]->getOrderItem());
        // Then the assignment's sold quantity should equal 5
        $this->assertEquals(5, $assignments[1]->getSoldQuantity());

        // Then the pending stock unit's ordered quantity should equal 15
        $this->assertEquals(15, $pendingStockUnit->getOrderedQuantity());
        // Then the pending stock unit's sold quantity should equal 15
        $this->assertEquals(15, $pendingStockUnit->getSoldQuantity());
        // Then the pending stock unit's net price should equal 14
        $this->assertEquals(14, $pendingStockUnit->getNetPrice());
        // Then the pending stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $pendingStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($pendingStockUnit->getStockAssignments()->toArray());
        // Then the pending stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[0]->getOrderItem());
        // Then the pending assignment's sold quantity should equal 15
        $this->assertEquals(15, $assignments[0]->getSoldQuantity());

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 5
        $this->assertEquals(5, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($newStockUnit->getStockAssignments()->toArray());
        // Then the new stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[0]->getOrderItem());
        // Then the new assignment's sold quantity should equal 5
        $this->assertEquals(5, $assignments[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit
     *  ...................    |-----------------|
     *  .                 .    | Ordered  (25) 0 |
     *  .                 .    | Sold         20 |
     *  .                 .    |                 |
     *  .     (25) 0      .....| Assignments     |
     *  .                 .    | |-------------| |
     *  .                 .    | |     20      | |
     *  .                 .    | |-------------| |
     *  ...................    |-----------------|
     */
    public function test_unlinkItem(): void
    {
        // Given the subject is ordered for 25 quantity with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 25, 12);

        // Given the subject has been sold for 20 quantity
        $orderItem = Fixtures::createOrderItem(20)->setOrder(
            Fixtures::createOrder('EUR', '-2 days')
        );
        $stockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(25)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierItem);
        Fixtures::createStockAssignment($stockUnit, $orderItem, 20);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getStockUnitResolverMock()
            ->method('findPendingOrReady')
            ->with($supplierItem)
            ->willReturn([]);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = Fixtures::createStockUnit();
        $this->getStockUnitResolverMock()
            ->method('createBySubjectRelative')
            ->with($supplierItem)
            ->willReturn($newStockUnit);

        // Test
        $this->linker->unlinkItem($supplierItem);

        // Then the supplier order item should be unlinked
        $this->assertNull($supplierItem->getStockUnit());
        // Then the stock unit should be unlinked
        $this->assertNull($stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 0
        $this->assertEquals(0, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $stockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($stockUnit->getStockAssignments()->toArray());
        // Then the linked stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItem, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 20
        $this->assertEquals(20, $assignments[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit       New stock unit
     *  ...................    ...................    |----------------|
     *  .                 .    . Ordered (25)  0 .    | Ordered      0 |
     *  .                 .    . Sold    (20)  0 .    | Sold   (10) 30 |
     *  .                 .    .                 .    |                |
     *  .     (25) 0      ...... Assignments     .    | Assignments    |
     *  .                 .    . ............... .    | |------------| |
     *  .                 .    . .     20      .------->|   (10) 30  | |
     *  .                 .    . ............... .    | |------------| |
     *  ...................    ...................    |----------------|
     */
    public function test_unlinkItem_byMovingAssignment(): void
    {
        // Given the subject is ordered for 25 quantity with a cost of 12 euros
        $supplierItem = Fixtures::createSupplierOrderItem(null, 25, 12)
            ->setOrder(Fixtures::createSupplierOrder(null, null, 'now'));

        // Given the subject has been sold for 20 quantity
        $orderItem = Fixtures::createOrderItem(20)->setOrder(
            Fixtures::createOrder()
        );
        $stockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(25)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierItem);
        Fixtures::createStockAssignment($stockUnit, $orderItem, 20);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getStockUnitResolverMock()
            ->method('findPendingOrReady')
            ->with($supplierItem)
            ->willReturn([]);

        // Given the stock unit resolver will return a linkable stock unit
        $linkableStockUnit = Fixtures::createStockUnit()
            ->setSoldQuantity(10);
        Fixtures::createStockAssignment($linkableStockUnit, $orderItem, 10);
        $this->getStockUnitResolverMock()
            ->method('findLinkable')
            ->with($supplierItem)
            ->willReturn($linkableStockUnit);

        // TODO Assert that stock unit will be removed
        /*$this->getPersistenceHelper()
            ->expects($this->atLeastOnce())
            ->method('remove')
            ->with($stockUnit, false);*/

        // Test
        $this->linker->unlinkItem($supplierItem);

        // Then the supplier order item should be unlinked
        $this->assertNull($supplierItem->getStockUnit());
        // Then the stock unit should be unlinked
        $this->assertNull($stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 0
        $this->assertEquals(0, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 0
        $this->assertEquals(0, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $stockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($stockUnit->getStockAssignments()->toArray());
        // Then the linked stock unit should have no assignment
        $this->assertCount(0, $assignments);

        // Then the linkable stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $linkableStockUnit->getOrderedQuantity());
        // Then the linkable stock unit's sold quantity should equal 30
        $this->assertEquals(30, $linkableStockUnit->getSoldQuantity());
        // Then the linkable stock unit's net price should equal 0
        $this->assertEquals(0, $linkableStockUnit->getNetPrice());
        // Then the linkable stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $stockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkableStockUnit->getStockAssignments()->toArray());
        // Then the linkable stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItem, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 30
        $this->assertEquals(30, $assignments[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit      Pending stock unit
     *  ...................    |-----------------|    |-----------------|
     *  .                 .    | Ordered (25)  0 |    | Ordered      20 |
     *  .                 .    | Sold    (20) 10 |    | Sold    (10) 20 |
     *  .                 .    |                 |    |                 |
     *  .     (25) 0      .....| Assignments     |    | Assignments     |
     *  .                 .    | |-------------| |    | |-------------| |
     *  .                 .    | |   (20) 10   |-|----->|   (10) 20   | |
     *  .                 .    | |-------------| |    | |-------------| |
     *  ...................    |-----------------|    |-----------------|
     */
    public function test_unlinkItem_bySplittingAssignment(): void
    {
        // Given the subject is ordered for 25 quantity with a cost of 12 euros
        $supplierItemA = Fixtures::createSupplierOrderItem(null, 25, 12)
            ->setOrder(Fixtures::createSupplierOrder(null, null, 'now'));

        // Given the subject has been sold for 20 quantity
        $orderItemA = Fixtures::createOrderItem(20)->setOrder(
            Fixtures::createOrder()
        );
        $stockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(25)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierItemA);
        Fixtures::createStockAssignment($stockUnit, $orderItemA, 20);

        // Given the stock unit resolver will return a pending stock unit
        $supplierItemB = Fixtures::createSupplierOrderItem(null, 20, 14)
            ->setOrder(Fixtures::createSupplierOrder(null, null, 'now'));
        $pendingStockUnit = Fixtures::createStockUnit()
            ->setOrderedQuantity(20)
            ->setSoldQuantity(10)
            ->setNetPrice(14)
            ->setSupplierOrderItem($supplierItemB);
        Fixtures::createStockAssignment($pendingStockUnit, $orderItemA, 10);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getStockUnitResolverMock()
            ->method('findPendingOrReady')
            ->with($supplierItemA)
            ->willReturn([$pendingStockUnit]);

        $this->getStockUnitResolverMock()
            ->method('findLinkable')
            ->with($supplierItemA)
            ->willReturn(null);

        // Test
        $this->linker->unlinkItem($supplierItemA);

        // Then the supplier order item should be unlinked
        $this->assertNull($supplierItemA->getStockUnit());
        // Then the stock unit should be unlinked
        $this->assertNull($stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 10
        $this->assertEquals(10, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 0
        $this->assertEquals(0, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $stockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($stockUnit->getStockAssignments()->toArray());
        // Then the linked stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 10
        $this->assertEquals(10, $assignments[0]->getSoldQuantity());

        // Then the pending stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $pendingStockUnit->getOrderedQuantity());
        // Then the pending stock unit's sold quantity should equal 20
        $this->assertEquals(20, $pendingStockUnit->getSoldQuantity());
        // Then the pending stock unit's net price should equal 14
        $this->assertEquals(14, $pendingStockUnit->getNetPrice());
        // Then the pending stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $pendingStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($pendingStockUnit->getStockAssignments()->toArray());
        // Then the pending stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 20
        $this->assertEquals(20, $assignments[0]->getSoldQuantity());
    }

    /**
     *  Supplier order item     Linked stock unit       New stock unit
     *  ...................    ...................     |----------------|
     *  .                 .    . Ordered (30)  0 .     | Ordered      0 |
     *  .                 .    . Sold    (30)  0 .     | Sold    (0) 25 |
     *  .                 .    .                 .     |                |
     *  .                 .    . Assignments     .     | Assignments    |
     *  .                 .    . ............... .     | |------------| |
     *  .     (30) 0      ...... .  (15) 0 [A] .-------->|     15     | |
     *  .                 .    . ............... .     | |------------| |
     *  .                 .    .                 .     |                |
     *  .                 .    . ............... .     | |------------| |
     *  .                 .    . . (15) 0 [B]  .-------->|     10     | |
     *  .                 .    . ............... .  |  | |------------| |
     *  ...................    ...................  |  |----------------|
     *                                              |
     *                         Pending stock unit   |
     *                         |----------------|   |
     *                         | Ordered     15 |   |
     *                         | Sold   (10) 15 |   |
     *                         |                |   |
     *                         | Assignments    |   |
     *                         | |------------| |   |
     *                         | |   10 (15)  |<----|
     *                         | |------------| |
     *                         |----------------|
     */
    public function test_unlinkItem_byMovingAndSplittingAssignment(): void
    {
        $this->markTestIncomplete(); // TODO
    }
}
