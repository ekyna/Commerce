<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Entity;

use Acme\Product\Entity\StockUnit;
use DateTime;
use Decimal\Decimal;
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
        $this->assertNull($adjustment->getStockUnit());

        $unitA = new StockUnit();
        $adjustment->setStockUnit($unitA);
        $this->assertSame($unitA, $adjustment->getStockUnit());
        $this->assertTrue($unitA->hasStockAdjustment($adjustment));

        $unitB = new StockUnit();
        $adjustment->setStockUnit($unitB);
        $this->assertSame($unitB, $adjustment->getStockUnit());
        $this->assertTrue($unitB->hasStockAdjustment($adjustment));
        $this->assertFalse($unitA->hasStockAdjustment($adjustment));

        $adjustment->setStockUnit(null);
        $this->assertNull($adjustment->getStockUnit());
        $this->assertFalse($unitB->hasStockAdjustment($adjustment));
        $this->assertFalse($unitA->hasStockAdjustment($adjustment));
    }

    public function test_quantity(): void
    {
        $adjustment = new StockAdjustment();
        $this->assertTrue($adjustment->getQuantity()->isZero());

        $adjustment->setQuantity(new Decimal(10));
        $this->assertTrue($adjustment->getQuantity()->equals('10'));
    }

    public function test_reason(): void
    {
        $adjustment = new StockAdjustment();
        $this->assertNull($adjustment->getReason());

        $adjustment->setReason('Foo');
        $this->assertSame('Foo', $adjustment->getReason());
    }

    public function test_note(): void
    {
        $adjustment = new StockAdjustment();
        $this->assertNull($adjustment->getNote());

        $adjustment->setNote('Foo');
        $this->assertSame('Foo', $adjustment->getNote());
    }

    public function test_createdAt(): void
    {
        $adjustment = new StockAdjustment();
        $this->assertInstanceOf(DateTime::class, $adjustment->getCreatedAt());

        $adjustment->setCreatedAt($date = new \DateTime('2020-01-01'));
        $this->assertSame($date, $adjustment->getCreatedAt());
    }
}
