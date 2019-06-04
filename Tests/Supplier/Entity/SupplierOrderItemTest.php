<?php

namespace Ekyna\Component\Commerce\Tests\Supplier\Entity;

use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderItem;
use PHPUnit\Framework\TestCase;

/**
 * Class SupplierOrderItemTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemTest extends TestCase
{
    public function test_setOrder_withOrder(): void
    {
        $item = new SupplierOrderItem();
        $order = new SupplierOrder();

        $item->setOrder($order);

        $this->assertEquals($order, $item->getOrder());
        $this->assertTrue($order->hasItem($item));
    }

    public function test_setOrder_withNull(): void
    {
        $item = new SupplierOrderItem();
        $order = new SupplierOrder();

        $item->setOrder($order);
        $item->setOrder(null);

        $this->assertNull($item->getOrder());
        $this->assertFalse($order->hasItem($item));
    }

    public function test_setOrder_withAnotherOrder(): void
    {
        $item = new SupplierOrderItem();
        $orderA = new SupplierOrder();
        $orderB = new SupplierOrder();

        $item->setOrder($orderA);
        $item->setOrder($orderB);

        $this->assertEquals($orderB, $item->getOrder());
        $this->assertTrue($orderB->hasItem($item));
        $this->assertFalse($orderA->hasItem($item));
    }
    
    public function test_setStockUnit_withUnit(): void
    {
        $item = new SupplierOrderItem();
        $unit = new StockUnit();

        $item->setStockUnit($unit);

        $this->assertEquals($unit, $item->getStockUnit());
        $this->assertEquals($item, $unit->getSupplierOrderItem());
    }

    public function test_setStockUnit_withNull(): void
    {
        $item = new SupplierOrderItem();
        $unit = new StockUnit();

        $item->setStockUnit($unit);
        $item->setStockUnit(null);

        $this->assertNull($item->getStockUnit());
        $this->assertNull($unit->getSupplierOrderItem());
    }

    public function test_setStockUnit_withAnotherUnit(): void
    {
        $item = new SupplierOrderItem();
        $unitA = new StockUnit();
        $unitB = new StockUnit();

        $item->setStockUnit($unitA);
        $item->setStockUnit($unitB);

        $this->assertEquals($unitB, $item->getStockUnit());
        $this->assertEquals($item, $unitB->getSupplierOrderItem());
        $this->assertNull($unitA->getSupplierOrderItem());
    }
}