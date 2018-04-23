<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Entity;

use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderItem;
use PHPUnit\Framework\TestCase;

/**
 * Class StockUnitTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitTest extends TestCase
{
    public function test_setSupplierOrderItem_withItem()
    {
        $unit = new StockUnit();
        $item = new SupplierOrderItem();

        $unit->setSupplierOrderItem($item);

        $this->assertEquals($item, $unit->getSupplierOrderItem());
        $this->assertEquals($unit, $item->getStockUnit());
    }

    public function test_setSupplierOrderItem_withNull()
    {
        $unit = new StockUnit();
        $item = new SupplierOrderItem();

        $unit->setSupplierOrderItem($item);
        $unit->setSupplierOrderItem(null);

        $this->assertNull($unit->getSupplierOrderItem());
        $this->assertNull($item->getStockUnit());
    }

    public function test_setSupplierOrderItem_withAnotherItem()
    {
        $unit = new StockUnit();
        $itemA = new SupplierOrderItem();
        $itemB = new SupplierOrderItem();

        $unit->setSupplierOrderItem($itemA);
        $unit->setSupplierOrderItem($itemB);

        $this->assertEquals($itemB, $unit->getSupplierOrderItem());
        $this->assertEquals($unit, $itemB->getStockUnit());
        $this->assertNull($itemA->getStockUnit());
    }
}