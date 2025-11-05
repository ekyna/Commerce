<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Order\Entity\OrderItem;
use Ekyna\Component\Commerce\Order\Entity\OrderItemAssignment;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderItemAssignmentTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAssignmentTest extends TestCase
{
    public function test_setStockUnit_withStockUnit()
    {
        $assignment = new OrderItemAssignment();
        $unit = new StockUnit();

        $assignment->setStockUnit($unit);

        $this->assertEquals($unit, $assignment->getStockUnit());
        $this->assertTrue($unit->hasStockAssignment($assignment));
    }

    public function test_setStockUnit_withNull()
    {
        $assignment = new OrderItemAssignment();
        $unit = new StockUnit();

        $assignment->setStockUnit($unit);
        $assignment->setStockUnit(null);

        $this->assertEquals(null, $assignment->getStockUnit());
        $this->assertFalse($unit->hasStockAssignment($assignment));
    }

    public function test_setStockUnit_withAnotherStockUnit()
    {
        $assignment = new OrderItemAssignment();
        $unitA = new StockUnit();
        $unitB = new StockUnit();

        $assignment->setStockUnit($unitA);
        $assignment->setStockUnit($unitB);

        $this->assertEquals($unitB, $assignment->getStockUnit());
        $this->assertTrue($unitB->hasStockAssignment($assignment));
        $this->assertFalse($unitA->hasStockAssignment($assignment));
    }

    public function test_setOrderItem_withOrderItem()
    {
        $assignment = new OrderItemAssignment();
        $item = new OrderItem();

        $assignment->setOrderItem($item);

        $this->assertEquals($item, $assignment->getOrderItem());
        $this->assertTrue($item->hasStockAssignment($assignment));
    }

    public function test_setOrderItem_withNull()
    {
        $assignment = new OrderItemAssignment();
        $item = new OrderItem();

        $assignment->setOrderItem($item);
        $assignment->setOrderItem(null);

        $this->assertEquals(null, $assignment->getOrderItem());
        $this->assertFalse($item->hasStockAssignment($assignment));
    }

    public function test_setOrderItem_withAnotherItem()
    {
        $assignment = new OrderItemAssignment();
        $itemA = new OrderItem();
        $itemB = new OrderItem();

        $assignment->setOrderItem($itemA);
        $assignment->setOrderItem($itemB);

        $this->assertEquals($itemB, $assignment->getOrderItem());
        $this->assertTrue($itemB->hasStockAssignment($assignment));
        $this->assertFalse($itemA->hasStockAssignment($assignment));
    }
}
