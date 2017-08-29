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
        $newStockUnit = new StockUnit();
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->linkItem($supplierOrderItem);

        // Then the supplier order item should be linked to a stock unit
        $this->assertNotNull($supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $newStockUnit->getSupplierOrderItem());
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
        $linkableStockUnit = (new StockUnit())->setSoldQuantity(10);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItem)
            ->setStockUnit($linkableStockUnit)
            ->setSoldQuantity(10);

        // Given the stock unit resolver will find the available stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($linkableStockUnit);

        // Given the stock unit resolver will return a new stock unit
//        $this->getUnitResolver()
//            ->method('createBySubjectRelative')
//            ->with($supplierOrderItem)
//            ->willReturn(new StockUnit());

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->linkItem($supplierOrderItem);
        $linkableStockUnit = $supplierOrderItem->getStockUnit();

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkableStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkableStockUnit->getSupplierOrderItem());
        // Then the stock unit's ordered quantity should equal 20
        $this->assertEquals(20, $linkableStockUnit->getOrderedQuantity());
        // Then the stock unit's sold quantity should equal 10
        $this->assertEquals(10, $linkableStockUnit->getSoldQuantity());
        // Then the stock unit's net price should equal 12
        $this->assertEquals(12, $linkableStockUnit->getNetPrice());
        // Then the stock unit's state should equal 'pending'
        $this->assertEquals(StockUnitStates::STATE_PENDING, $linkableStockUnit->getState());

        /** @var OrderItemStockAssignment[] $assignments */
        $assignments = array_values($linkableStockUnit->getStockAssignments()->toArray());
        // Then the stock unit should have one assignment
        $this->assertCount(1, $assignments);
        // Then the assignment should be associated with the order item
        $this->assertEquals($orderItem, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 10
        $this->assertEquals(10, $assignments[0]->getSoldQuantity());
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
        $date = new \DateTime();
        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-2 day')));
        $orderItemB = (new OrderItem())
            ->setQuantity(10)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $linkableStockUnit = (new StockUnit())->setSoldQuantity(30);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkableStockUnit)
            ->setSoldQuantity(20);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($linkableStockUnit)
            ->setSoldQuantity(10);

        // Given the stock unit resolver will find the available stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($linkableStockUnit);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->linkItem($supplierOrderItem);

        // Order item B and his assignment should be moved from the new stock unit to the linkable stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkableStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkableStockUnit->getSupplierOrderItem());
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

        // Given the subject has been sold for 30 quantity
        $date = new \DateTime();
        $orderItemA = (new OrderItem())
            ->setQuantity(15)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-2 day')));
        $orderItemB = (new OrderItem())
            ->setQuantity(15)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $linkableStockUnit = (new StockUnit())->setSoldQuantity(30);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkableStockUnit)
            ->setSoldQuantity(15);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($linkableStockUnit)
            ->setSoldQuantity(15);

        // Given the stock unit resolver will find the available stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($linkableStockUnit);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Tests
        $linker = $this->createStockUnitLinker();
        $linker->linkItem($supplierOrderItem);

        // Order item B's assignment should be split into the new stock unit for 10 quantity

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkableStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkableStockUnit->getSupplierOrderItem());
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
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_positive_change()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 25 quantity (20 before update) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(25)
            ->setOrder($supplierOrder);

        // Given the subject has been ordered for 20 quantity
        $linkedStockUnit = (new StockUnit())
            ->setSupplierOrderItem($supplierOrderItem)
            ->setSoldQuantity(20)
            ->setOrderedQuantity(20)
            ->setNetPrice(12)
            ->setState(StockUnitStates::STATE_PENDING);

        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt(new \DateTime()));
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(20);

        // Given the supplier order item's quantity has changed from 20 to 25 (+8)
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn([20, 25]);

        // TODO test price change

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkedStockUnit->getSupplierOrderItem());
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
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_positive_change_by_moving_assignment()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 30 quantity (20 before change) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(30)
            ->setOrder($supplierOrder);

        $date = new \DateTime();

        // Given the subject has been sold for 30 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-2 day')));
        $orderItemB = (new OrderItem())
            ->setQuantity(10)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));

        $linkedStockUnit = (new StockUnit())
            ->setOrderedQuantity(20)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(20);

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

        // Given the stock unit resolver will return a new stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Assert that stock unit B will be removed
        $this->getPersistenceHelper()
            ->expects($this->once())
            ->method('remove')
            ->with($newStockUnit, false);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);

        // Order item B ans his assignment should be moved from the new stock unit into the linked stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkedStockUnit->getSupplierOrderItem());
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
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_positive_change_by_splitting_assignment()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 30 quantity (20 before change) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(30)
            ->setOrder($supplierOrder);

        $date = new \DateTime();

        // Given the subject has been sold for 40 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(15)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-2 day')));
        $orderItemB = (new OrderItem())
            ->setQuantity(25)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));

        $linkedStockUnit = (new StockUnit())
            ->setOrderedQuantity(20)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(15);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(5);

        $newStockUnit = (new StockUnit())
            ->setOrderedQuantity(0)
            ->setSoldQuantity(20);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($newStockUnit)
            ->setSoldQuantity(20);

        // Given the supplier order item's quantity has changed from 20 to 30
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn([20, 30]);

        // Given the stock unit resolver will return a new stock unit
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($newStockUnit);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);

        // Order item B ans his assignment should be split from the new stock unit into the linked stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkedStockUnit->getSupplierOrderItem());
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
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_negative_change()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity (25 before update) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been ordered for 20 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt(new \DateTime()));

        $linkedStockUnit = (new StockUnit())
            ->setSupplierOrderItem($supplierOrderItem)
            ->setSoldQuantity(20)
            ->setOrderedQuantity(25)
            ->setNetPrice(12)
            ->setState(StockUnitStates::STATE_PENDING);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(20);

        // Given the supplier order item's quantity has changed from 20 to 18 (-2)
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItem, 'quantity')
            ->willReturn([25, 20]);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItem);

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkedStockUnit->getSupplierOrderItem());
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
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_negative_change_by_moving_assignment()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity (30 before change) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 30
        $date = new \DateTime();
        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-2 day')));
        $orderItemB = (new OrderItem())
            ->setQuantity(10)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $linkedStockUnit = (new StockUnit())
            ->setOrderedQuantity(30)
            ->setSoldQuantity(30)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(20);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(10);

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

        // Order item B ans his assignment should be moved from the linked stock unit into the new stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkedStockUnit->getSupplierOrderItem());
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
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_negative_change_by_splitting_assignment()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity (30 before change) with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 30
        $date = new \DateTime();
        $orderItemA = (new OrderItem())
            ->setQuantity(15)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-2 day')));
        $orderItemB = (new OrderItem())
            ->setQuantity(15)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $linkedStockUnit = (new StockUnit())
            ->setOrderedQuantity(30)
            ->setSoldQuantity(30)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(15);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(15);

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

        // Order item B ans his assignment should be split from the linked stock unit into the new stock unit

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierOrderItem->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItem, $linkedStockUnit->getSupplierOrderItem());
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
     * @covers StockUnitLinker::applyItem()
     */
    public function test_it_applies_item_with_negative_change_by_moving_and_splitting_assignment()
    {
        // Given the supplier order is submitted
        $supplierOrderA = new SupplierOrder();
        $supplierOrderA->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 20 quantity (30 before change) with a cost of 12 euros
        $supplierOrderItemA = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(20)
            ->setOrder($supplierOrderA);

        // Given the subject has been sold for 30
        $date = new \DateTime();
        $orderItemA = (new OrderItem())
            ->setQuantity(15)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-2 day')));
        $orderItemB = (new OrderItem())
            ->setQuantity(15)
            ->setOrder((new Order())->setCreatedAt((clone $date)->modify('-1 day')));
        $linkedStockUnit = (new StockUnit())
            ->setOrderedQuantity(30)
            ->setSoldQuantity(30)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItemA);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(15);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($linkedStockUnit)
            ->setSoldQuantity(15);

        // Given the supplier order item's quantity has changed from 30 to 20
        $this->getPersistenceHelper()
            ->method('isChanged')
            ->with($supplierOrderItemA, 'quantity')
            ->willReturn(true);
        $this->getPersistenceHelper()
            ->method('getChangeSet')
            ->with($supplierOrderItemA, 'quantity')
            ->willReturn([30, 20]);

        // Given the stock unit resolver will return a pending stock unit
        $supplierOrderItemB = (new SupplierOrderItem())
            ->setNetPrice(14)
            ->setQuantity(15)
            ->setOrder((new SupplierOrder())->setOrderedAt(new \DateTime()));
        $pendingStockUnit = new StockUnit();
        $pendingStockUnit
            ->setOrderedQuantity(15)
            ->setSoldQuantity(10)
            ->setNetPrice(14)
            ->setSupplierOrderItem($supplierOrderItemB);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemB)
            ->setStockUnit($pendingStockUnit)
            ->setSoldQuantity(10);
        $this->getUnitResolver()
            ->method('findPendingOrReady')
            ->with($supplierOrderItemA)
            ->willReturn([$pendingStockUnit]);

        // Given the stock unit resolver will return a new stock unit
        $newStockUnit = new StockUnit();
        $this->getUnitResolver()
            ->method('createBySubjectRelative')
            ->with($supplierOrderItemA)
            ->willReturn($newStockUnit);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->applyItem($supplierOrderItemA);

        // Order item B ans his assignment should be
        // split from the linked stock unit into the pending stock unit for 5 quantity and
        // split from the linked stock unit into the new stock unit for 5 quantity

        // Then the supplier order item should be linked to the stock unit
        $this->assertEquals($linkedStockUnit, $supplierOrderItemA->getStockUnit());
        // Then the stock unit should be linked to the supplier order item
        $this->assertEquals($supplierOrderItemA, $linkedStockUnit->getSupplierOrderItem());
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
     * @covers StockUnitLinker::unlinkItem()
     */
    public function test_it_unlinks_item()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 25 quantity with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(25)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 20 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt($date = new \DateTime()));
        $stockUnit = (new StockUnit())
            ->setOrderedQuantity(25)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(20);

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
        $linker->unlinkItem($supplierOrderItem);

        // Then the supplier order item should be unlinked
        $this->assertNull($supplierOrderItem->getStockUnit());
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
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 20
        $this->assertEquals(20, $assignments[0]->getSoldQuantity());
    }

    /**
     * @covers StockUnitLinker::unlinkItem()
     */
    public function test_it_unlinks_item_by_moving_assignment()
    {
        // Given the supplier order is submitted
        $supplierOrder = new SupplierOrder();
        $supplierOrder->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 25 quantity with a cost of 12 euros
        $supplierOrderItem = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(25)
            ->setOrder($supplierOrder);

        // Given the subject has been sold for 20 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt($date = new \DateTime()));
        $stockUnit = (new StockUnit())
            ->setOrderedQuantity(25)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItem);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(20);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getUnitResolver()
            ->method('findPendingOrReady')
            ->with($supplierOrderItem)
            ->willReturn([]);

        // Given the stock unit resolver will return a linkable stock unit
        $linkableStockUnit = (new StockUnit())
            ->setSoldQuantity(10);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($linkableStockUnit)
            ->setSoldQuantity(10);
        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItem)
            ->willReturn($linkableStockUnit);

        // TODO Assert that stock unit will be removed
        /*$this->getPersistenceHelper()
            ->expects($this->atLeastOnce())
            ->method('remove')
            ->with($stockUnit, false);*/

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->unlinkItem($supplierOrderItem);

        // Then the supplier order item should be unlinked
        $this->assertNull($supplierOrderItem->getStockUnit());
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
        $this->assertEquals($orderItemA, $assignments[0]->getOrderItem());
        // Then the assignment's sold quantity should equal 30
        $this->assertEquals(30, $assignments[0]->getSoldQuantity());
    }

    /**
     * @covers StockUnitLinker::unlinkItem()
     */
    public function test_it_unlinks_item_by_splitting_assignment()
    {
        // Given the supplier A order is submitted
        $supplierOrderA = new SupplierOrder();
        $supplierOrderA->setOrderedAt(new \DateTime());

        // Given the subject is ordered for 25 quantity with a cost of 12 euros
        $supplierOrderItemA = (new SupplierOrderItem())
            ->setNetPrice(12)
            ->setQuantity(25)
            ->setOrder($supplierOrderA);

        // Given the subject has been sold for 20 quantity
        $orderItemA = (new OrderItem())
            ->setQuantity(20)
            ->setOrder((new Order())->setCreatedAt($date = new \DateTime()));
        $stockUnit = (new StockUnit())
            ->setOrderedQuantity(25)
            ->setSoldQuantity(20)
            ->setNetPrice(12)
            ->setSupplierOrderItem($supplierOrderItemA);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($stockUnit)
            ->setSoldQuantity(20);

        // Given the stock unit resolver will return a pending stock unit
        $supplierOrderItemB = (new SupplierOrderItem())
            ->setNetPrice(14)
            ->setQuantity(20)
            ->setOrder((new SupplierOrder())->setOrderedAt(new \DateTime()));
        $pendingStockUnit = (new StockUnit())
            ->setOrderedQuantity(20)
            ->setSoldQuantity(10)
            ->setNetPrice(14)
            ->setSupplierOrderItem($supplierOrderItemB);
        (new OrderItemStockAssignment())
            ->setOrderItem($orderItemA)
            ->setStockUnit($pendingStockUnit)
            ->setSoldQuantity(10);

        // Given the stock unit resolver will return no 'pending or ready' stock unit
        $this->getUnitResolver()
            ->method('findPendingOrReady')
            ->with($supplierOrderItemA)
            ->willReturn([$pendingStockUnit]);

        $this->getUnitResolver()
            ->method('findLinkable')
            ->with($supplierOrderItemA)
            ->willReturn(null);

        // Test
        $linker = $this->createStockUnitLinker();
        $linker->unlinkItem($supplierOrderItemA);

        // Then the supplier order item should be unlinked
        $this->assertNull($supplierOrderItemA->getStockUnit());
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
