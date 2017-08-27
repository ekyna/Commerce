<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Linker;

use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderItem;
use Ekyna\Component\Commerce\Order\Entity\OrderItemStockAssignment;
use Ekyna\Component\Commerce\Stock\Linker\StockUnitLinker;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderItem;
use Ekyna\Component\Commerce\Tests\BaseTestCase;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitLinkerTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitLinkerTest extends BaseTestCase
{
    /**
     * @covers StockUnitLinker::linkItem()
     */
    public function test_links_item()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the stock unit resolver will not find an available stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn(null);

        // Given the stock unit resolver will return a new stock unit
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn(new StockUnit());

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->linkItem($supplierOrderItem);

        // Then the supplier order item should be linked to a stock unit
        $this->assertNotNull($stockUnit = $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 0
        $this->assertEquals(0, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());
        // Then the stock unit should not have assignment
        $this->assertEmpty($stockUnit->getStockAssignments());
    }

    /**
     * @covers StockUnitLinker::linkItem()
     */
    public function test_it_links_item_while_no_overflow()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 10 quantity
        $orderItem = (new OrderItem())->setQuantity(10);
        $stockUnit = (new StockUnit())->setSoldQuantity(10);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItem)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(10);

        // Given the stock unit resolver will find the available stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($stockUnit);

        // Given the stock unit resolver will return a new stock unit
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn(new StockUnit());

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->linkItem($supplierOrderItem);
        $stockUnit = $supplierOrderItem->getStockUnit();

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($stockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 10
        $this->assertEquals(10, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());
        // Then the stock unit should have one assignment
        $this->assertCount(1, $stockUnit->getStockAssignments());
        // Then the assignment should be associated with the order item
        $this->assertEquals($orderItem, $stockUnit->getStockAssignments()[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 10
        $this->assertEquals(10, $stockUnit->getStockAssignments()[0]->getSoldQuantity());
    }

    /**
     * @covers StockUnitLinker::linkItem()
     */
    public function test_it_links_item_by_moving_assignment()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 20 quantity and 10 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(10)
            ->setOrder((new Order())->setCreatedAt($date = new \DateTime()));
        $orderItemB = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $stockUnit = (new StockUnit())->setSoldQuantity(30);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(10);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(20);

        // Given the stock unit resolver will find the available stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($stockUnit);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->linkItem($supplierOrderItem);

        // Order item A and his assignment should be moved to the new stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($stockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($stockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[0]->getOrderItem());
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

        /** @var OrderItemStockAssignment[] $newAssignments */
        $newAssignments = array_values($newStockUnit->getStockAssignments()->toArray());
        // Then the new stock unit should have one assignment
        $this->assertCount(1, $newAssignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $newAssignments[0]->getOrderItem());
        // Then the new assignment's sold quantity should equal 10
        $this->assertEquals(10, $newAssignments[0]->getSoldQuantity());
    }

    /**
     * @covers StockUnitLinker::linkItem()
     */
    public function test_it_links_item_by_splitting_assignment()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 15 quantity and 10 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(10)
            ->setOrder((new Order())->setCreatedAt($date = new \DateTime()));
        $orderItemB = (new OrderItem())
            ->setQuantity(15)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $stockUnit = (new StockUnit())->setSoldQuantity(25);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(10);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(15);

        // Given the stock unit resolver will find the available stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($stockUnit);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Tests
        $linker = $this->createStockUnitLinker();
        $linker->linkItem($supplierOrderItem);

        // Order item A's assignment should be split into the new stock unit for 5 quantity

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($stockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($stockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have two assignment
        $this->assertCount(2, $assignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the first assignment's sold quantity should equal 15
        $this->assertEquals(5, $assignments[0]->getSoldQuantity());
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[1]->getOrderItem());
        // Then the second assignment's sold quantity should equal 5
        $this->assertEquals(15, $assignments[1]->getSoldQuantity());

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 5
        $this->assertEquals(5, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());

        /** @var OrderItemStockAssignment[] $newAssignments */
        $newAssignments = array_values($newStockUnit->getStockAssignments()->toArray());
        // Then the new stock unit should have one assignment
        $this->assertCount(1, $newAssignments);
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $newAssignments[0]->getOrderItem());
        // Then the new assignment's sold quantity should equal 5
        $this->assertEquals(5, $newAssignments[0]->getSoldQuantity());
    }

    /**
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_positive_change()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity (18 before update) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been ordered for 18 quantity
        $stockUnit = (new StockUnit())
            ->setSupplierOrderItem($supplierOrderItem)
            ->setSoldQuantity(0)
            ->setOrderedQuantity(18)
            ->setNetPrice(12)
            ->setState(StockUnitStates::STATE_PENDING);

        // Given the supplier order item's quantity has changed from 18 to 20 (+2)
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn([18, 20]);

        // TODO test price change

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($stockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 0
        $this->assertEquals(0, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());
        // Then the stock unit should have no assignment
        $this->assertCount(0, $stockUnit->getStockAssignments());
    }

    /**
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_negative_change()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 18 quantity (20 before update) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(18)
            ->setOrder($supplierOrder);

        // Given the subject has been ordered for 18 quantity
        $stockUnit = (new StockUnit())
            ->setSupplierOrderItem($supplierOrderItem)
            ->setSoldQuantity(0)
            ->setOrderedQuantity(20)
            ->setNetPrice(12)
            ->setState(StockUnitStates::STATE_PENDING);

        // Given the supplier order item's quantity has changed from 20 to 18 (-2)
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn([20, 18]);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($stockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(18, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 0
        $this->assertEquals(0, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());
        // Then the stock unit should have no assignment
        $this->assertCount(0, $stockUnit->getStockAssignments());
    }

    /**
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_while_no_overflow()
    {
        $this->markTestIncomplete('Not yet implemented');
    }

    /**
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_positive_change_by_moving_assignment_to_new_stock_unit()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 30 quantity (20 before change) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(30)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 20 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            /*->setOrder((new Order())->setCreatedAt($date = new \DateTime()))*/;
        $stockUnit = (new StockUnit())
            ->setOrderedQuantity(20)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(20);

        $orderItemB = (new OrderItem())
            ->setQuantity(10)
            ->setOrder((new Order())->setCreatedAt($date = new \DateTime()));
        $newStockUnit = (new StockUnit())
            ->setOrderedQuantity(0)
            ->setSoldQuantity(10);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($newStockUnit)
            ->setSoldQuantity(10);

        // Given the supplier order item's quantity has changed from 20 to 30
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn([20, 30]);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
//        $this->getUnitResolver()
//            ->method('findPendingOrReady')
//            ->with($supplierOrderItem)
//            ->willReturn([]);

        // Given the stock unit resolver will return a new stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);

        // Order item B ans his assignment should be moved from the new stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($stockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(30, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(30, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());
        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($stockUnit->getStockAssignments()->toArray());
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

        // TODO Assert that stock unit B has been removed

        // Then the new stock unit's ordered quantity should equal 0
        $this->assertEquals(0, $newStockUnit->getOrderedQuantity());
        // Then the new stock unit's sold quantity should equal 10
        $this->assertEquals(0, $newStockUnit->getSoldQuantity());
        // Then the new stock unit's net price should equal 0
        $this->assertEquals(0, $newStockUnit->getNetPrice());
        // Then the new stock unit's state should equal 'new'
        $this->assertEquals(StockUnitStates::STATE_NEW, $newStockUnit->getState());
        /** @var OrderItemStockAssignment[] $newAssignments */
        $newAssignments = array_values($newStockUnit->getStockAssignments()->toArray());
        // Then the new stock unit should have one assignment
        $this->assertCount(0, $newAssignments);
    }

    /**
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_by_splitting_assignment()
    {
        $this->markTestIncomplete('Not yet implemented');
    }

    /**
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_negative_change_by_moving_assignment_to_new_stock_unit()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity (30 before change) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 20 quantity and 10 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(10)
            ->setOrder((new Order())->setCreatedAt($date = new \DateTime()));
        $orderItemB = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $stockUnit = (new StockUnit())
            ->setOrderedQuantity(30)
            ->setSoldQuantity(30)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(10);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(20);

        // Given the supplier order item's quantity has changed from 30 to 20
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn([30, 20]);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getUnitResolver()
            ->method('findPendingOrReady')
            ->with($supplierOrderItem)
            ->willReturn([]);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);

        // Order item A ans his assignment should be moved into the new stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($stockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $stockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $stockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 20
        $this->assertEquals(20, $stockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $stockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $stockUnit->getState());
        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($stockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item B
        $this->assertEquals($orderItemB, $assignments[0]->getOrderItem());
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
        // Then the assignment should be associated with the order item A
        $this->assertEquals($orderItemA, $newStockUnit->getStockAssignments()[0]->getOrderItem());
        // Then the new assignment's sold quantity should equal 10
        $this->assertEquals(10, $newStockUnit->getStockAssignments()[0]->getSoldQuantity());
    }

    public function test_it_unlinks_item()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity (30 before change) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 20 quantity and 10 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(10)
            ->setOrder((new Order())->setCreatedAt($date = new \DateTime()));
        $orderItemB = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $stockUnit = (new StockUnit())
            ->setOrderedQuantity(30)
            ->setSoldQuantity(30)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(10);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(20);

        // Given the supplier order item's quantity has changed from 30 to 20
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn([30, 20]);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getUnitResolver()
            ->method('findPendingOrReady')
            ->with($supplierOrderItem)
            ->willReturn([]);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);
    }

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|StockUnitResolverInterface
     */
    private $unitResolver;

    /**
     * Returns the persistence helper.
     *
     * @return PersistenceHelperInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getPersistenceHelper()
    {
        if (null !== $this->persistenceHelper) {
            return $this->persistenceHelper;
        }

        return $this->persistenceHelper = $this->createMock(PersistenceHelperInterface::class);
    }

    /**
     * Returns the unit resolver.
     *
     * @return StockUnitResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getUnitResolver()
    {
        if (null !== $this->unitResolver) {
            return $this->unitResolver;
        }

        return $this->unitResolver = $this->createMock(StockUnitResolverInterface::class);
    }

    /**
     * Creates a stock unit linker.
     *
     * @return StockUnitLinker
     */
    private function createStockUnitLinker()
    {
        $unitStateResolver = new StockUnitStateResolver();

        return new StockUnitLinker(
            $this->getPersistenceHelper(),
            $this->getUnitResolver(),
            $unitStateResolver,
            $this->createSaleFactory()
        );
    }
}
