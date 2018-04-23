<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Entity;

use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Stock\Entity\StockAdjustment;
use PHPUnit\Framework\TestCase;

/**
 * Class StockAdjustmentTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentTest extends TestCase
{
    public function test_setStockUnit_withUnit()
    {
        $adjustment = new StockAdjustment();
        $unit = new StockUnit();

        $adjustment->setStockUnit($unit);

        $this->assertEquals($unit, $adjustment->getStockUnit());
        $this->assertTrue($unit->hasStockAdjustment($adjustment));
    }

    public function test_setStockUnit_withNull()
    {
        $adjustment = new StockAdjustment();
        $unit = new StockUnit();

        $adjustment->setStockUnit($unit);
        $adjustment->setStockUnit(null);

        $this->assertNull($adjustment->getStockUnit());
        $this->assertFalse($unit->hasStockAdjustment($adjustment));
    }

    public function test_setStockUnit_withAnotherUnit()
    {
        $adjustment = new StockAdjustment();
        $unitA = new StockUnit();
        $unitB = new StockUnit();

        $adjustment->setStockUnit($unitA);
        $adjustment->setStockUnit($unitB);

        $this->assertEquals($unitB, $adjustment->getStockUnit());
        $this->assertTrue($unitB->hasStockAdjustment($adjustment));
        $this->assertFalse($unitA->hasStockAdjustment($adjustment));
    }
}