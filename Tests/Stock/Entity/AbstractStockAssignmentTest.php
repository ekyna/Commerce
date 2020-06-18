<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Entity;

use Ekyna\Component\Commerce\Tests\Fixture;
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
        $assignment = Fixture::stockAssignment();
        $this->assertNull($assignment->getStockUnit());

        $unitA = Fixture::stockUnit();
        $assignment->setStockUnit($unitA);
        $this->assertSame($unitA, $assignment->getStockUnit());
        $this->assertTrue($unitA->hasStockAssignment($assignment));

        $unitB = Fixture::stockUnit();
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
        $assignment = Fixture::stockAssignment();
        $this->assertSame(0., $assignment->getSoldQuantity());

        $assignment->setSoldQuantity(10);
        $this->assertSame(10., $assignment->getSoldQuantity());
    }

    public function test_shippedQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        $this->assertSame(0., $assignment->getShippedQuantity());

        $assignment->setShippedQuantity(10);
        $this->assertSame(10., $assignment->getShippedQuantity());
    }

    public function test_lockedQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        $this->assertSame(0., $assignment->getLockedQuantity());

        $assignment->setLockedQuantity(10);
        $this->assertSame(10., $assignment->getLockedQuantity());
    }

    public function test_getShippableQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        $this->assertSame(0., $assignment->getShippableQuantity());

        $assignment = Fixture::stockAssignment([
            'sold' => 10.,
        ]);
        $this->assertSame(0., $assignment->getShippableQuantity()); // No stock unit

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20.,
                'received' => 20.,
            ],
            'sold' => 10.,
        ]);
        $this->assertSame(10., $assignment->getShippableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20.,
                'received' => 20.,
            ],
            'sold'    => 10.,
            'shipped' => 5.,
        ]);
        $this->assertSame(5., $assignment->getShippableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20.,
                'received' => 20.,
            ],
            'sold'    => 10.,
            'shipped' => 5.,
            'locked'  => 5.,
        ]);
        $this->assertSame(0., $assignment->getShippableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20.,
                'received' => 20.,
            ],
            'sold'    => 40.,
            'shipped' => 5.,
            'locked'  => 5.,
        ]);
        $this->assertSame(20., $assignment->getShippableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20.,
                'received' => 20.,
                'shipped'  => 10.,
                'locked'   => 5.,
            ],
            'sold' => 40.,
        ]);
        $this->assertSame(5., $assignment->getShippableQuantity());
    }

    public function test_getReleasableQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        $this->assertsame(0., $assignment->getReleasableQuantity());

        $assignment = Fixture::stockAssignment([
            'sold' => 20.,
        ]);
        $this->assertsame(0., $assignment->getReleasableQuantity()); // No stock unit

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'sold' => 20.,
            ],
            'sold' => 20.,
        ]);
        $this->assertsame(20., $assignment->getReleasableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'sold' => 20.,
            ],
            'sold'    => 20.,
            'shipped' => 5.,
        ]);
        $this->assertsame(15., $assignment->getReleasableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'sold'    => 50.,
                'shipped' => 10.,
            ],
            'sold' => 20.,
        ]);
        $this->assertsame(20., $assignment->getReleasableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'sold' => 50.,
            ],
            'sold'    => 20.,
            'shipped' => 5.,
            'locked'  => 5.,
        ]);
        $this->assertsame(10., $assignment->getReleasableQuantity());
    }

    public function test_isFullyShipped(): void
    {
        $assignment = Fixture::stockAssignment();
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
        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20,
                'received' => 20,
            ],
        ]);

        $this->assertTrue($assignment->isFullyShippable());

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20,
                'received' => 20,
            ],
            'sold' => 20,
        ]);
        $this->assertTrue($assignment->isFullyShippable());

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20,
                'received' => 20,
            ],
            'sold' => 30,
        ]);
        $this->assertFalse($assignment->isFullyShippable()); // Limited by the stock unit

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20,
                'received' => 20,
                'shipped'  => 10,
            ],
            'sold'    => 20,
            'shipped' => 10,
        ]);
        $this->assertTrue($assignment->isFullyShippable());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20,
                'received' => 20,
                'shipped'  => 10,
                'locked'   => 10,
            ],
            'sold'    => 20,
            'shipped' => 10,
            'locked'  => 10,
        ]);
        $this->assertTrue($assignment->isFullyShippable());
    }

    public function test_isEmpty(): void
    {
        $assignment = Fixture::stockAssignment();
        $this->assertTrue($assignment->isEmpty());

        $assignment->setSoldQuantity(10);
        $this->assertFalse($assignment->isEmpty());
    }
}
