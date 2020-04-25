<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Entity;

use Acme\Product\Entity\StockUnit;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Entity\AbstractStockAssignment;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class StockAssignmentTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Entity
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AbstractStockAssignmentTest extends TestCase
{
    public function test_stockUnit(): void
    {
        $assignment = $this->create();
        $this->assertNull($assignment->getStockUnit());

        $unitA = new StockUnit();
        $assignment->setStockUnit($unitA);
        $this->assertSame($unitA, $assignment->getStockUnit());
        $this->assertTrue($unitA->hasStockAssignment($assignment));

        $unitB = new StockUnit();
        $assignment->setStockUnit($unitB);
        $this->assertSame($unitB, $assignment->getStockUnit());
        $this->assertTrue($unitB->hasStockAssignment($assignment));
        $this->assertFalse($unitA->hasStockAssignment($assignment));

        $assignment->setStockUnit(null);
        $this->assertNull($assignment->getStockUnit());
        $this->assertFalse($unitB->hasStockAssignment($assignment));
    }

    public function test_soldQuantity(): void
    {
        $assignment = $this->create();
        $this->assertSame(.0, $assignment->getSoldQuantity());

        $assignment->setSoldQuantity(10);
        $this->assertSame(10., $assignment->getSoldQuantity());
    }

    public function test_shippedQuantity(): void
    {
        $assignment = $this->create();
        $this->assertSame(.0, $assignment->getShippedQuantity());

        $assignment->setShippedQuantity(10);
        $this->assertSame(10., $assignment->getShippedQuantity());
    }

    public function test_getShippableQuantity(): void
    {
        $assignment = $this->create();
        $this->assertSame(.0, $assignment->getShippableQuantity());

        $assignment->setSoldQuantity(10);
        $this->assertSame(.0, $assignment->getShippableQuantity()); // No stock unit

        $unit = $this->createMock(StockUnitInterface::class);
        $unit
            ->expects($this->any())
            ->method('getShippableQuantity')
            ->willReturn(20.);

        $assignment->setStockUnit($unit);
        $this->assertSame(10., $assignment->getShippableQuantity());

        $assignment->setShippedQuantity(5);
        $this->assertSame(5., $assignment->getShippableQuantity());

        $assignment->setSoldQuantity(30);
        $this->assertSame(20., $assignment->getShippableQuantity());

        $assignment->setShippedQuantity(20);
        $this->assertSame(10., $assignment->getShippableQuantity());

        $assignment->setSoldQuantity(50);
        $assignment->setShippedQuantity(10);
        $this->assertSame(20., $assignment->getShippableQuantity());
    }

    public function test_isFullyShipped(): void
    {
        $assignment = $this->create();
        $this->assertTrue($assignment->isFullyShipped());

        $assignment->setSoldQuantity(20);
        $this->assertFalse($assignment->isFullyShipped());

        $assignment->setShippedQuantity(10);
        $this->assertFalse($assignment->isFullyShipped());

        $assignment->setShippedQuantity(20);
        $this->assertTrue($assignment->isFullyShipped());
    }

    public function test_isFullyShippable(): void
    {
        $assignment = $this->create();
        $unit = $this->createMock(StockUnitInterface::class);
        $unit
            ->expects($this->any())
            ->method('getShippableQuantity')
            ->willReturn(20.);
        $assignment->setStockUnit($unit);

        $this->assertTrue($assignment->isFullyShippable());

        $assignment->setSoldQuantity(20);
        $this->assertTrue($assignment->isFullyShippable());

        $assignment->setSoldQuantity(30);
        $this->assertFalse($assignment->isFullyShippable()); // Limited by the stock unit

        $assignment->setShippedQuantity(10);
        $this->assertTrue($assignment->isFullyShippable());
    }

    public function test_isEmpty(): void
    {
        $assignment = $this->create();
        $this->assertTrue($assignment->isEmpty());

        $assignment->setSoldQuantity(10);
        $this->assertFalse($assignment->isEmpty());
    }

    private function create(): StockAssignmentInterface
    {
        return new class extends AbstractStockAssignment {
            public function getSaleItem()
            {
            }

            public function setSaleItem(SaleItemInterface $saleItem = null)
            {
            }
        };
    }
}
