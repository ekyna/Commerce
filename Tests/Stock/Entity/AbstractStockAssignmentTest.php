<?php /** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Stock\Entity;

use Decimal\Decimal;
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
        self::assertNull($assignment->getStockUnit());

        $unitA = Fixture::stockUnit();
        $assignment->setStockUnit($unitA);
        self::assertSame($unitA, $assignment->getStockUnit());
        self::assertTrue($unitA->hasStockAssignment($assignment));

        $unitB = Fixture::stockUnit();
        $assignment->setStockUnit($unitB);
        self::assertSame($unitB, $assignment->getStockUnit());
        self::assertTrue($unitB->hasStockAssignment($assignment));
        self::assertFalse($unitA->hasStockAssignment($assignment));

        $assignment->setStockUnit(null);
        self::assertNull($assignment->getStockUnit());
        self::assertFalse($unitB->hasStockAssignment($assignment));
    }

    public function test_soldQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        self::assertTrue($assignment->getSoldQuantity()->isZero());

        $assignment->setSoldQuantity(new Decimal(10));
        self::assertEquals(new Decimal(10), $assignment->getSoldQuantity());
    }

    public function test_shippedQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        self::assertTrue($assignment->getShippedQuantity()->isZero());

        $assignment->setShippedQuantity(new Decimal(10));
        self::assertEquals(new Decimal(10), $assignment->getShippedQuantity());
    }

    public function test_lockedQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        self::assertTrue($assignment->getLockedQuantity()->isZero());

        $assignment->setLockedQuantity(new Decimal(10));
        self::assertEquals(new Decimal(10), $assignment->getLockedQuantity());
    }

    public function test_getShippableQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        self::assertTrue($assignment->getShippableQuantity()->isZero());

        $assignment = Fixture::stockAssignment([
            'sold' => 10.,
        ]);
        self::assertTrue($assignment->getShippableQuantity()->isZero()); // No stock unit

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20.,
                'received' => 20.,
            ],
            'sold' => 10.,
        ]);
        self::assertEquals(new Decimal(10), $assignment->getShippableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20.,
                'received' => 20.,
            ],
            'sold'    => 10.,
            'shipped' => 5.,
        ]);
        self::assertEquals(new Decimal(5), $assignment->getShippableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20.,
                'received' => 20.,
            ],
            'sold'    => 10.,
            'shipped' => 5.,
            'locked'  => 5.,
        ]);
        self::assertTrue($assignment->getShippableQuantity()->isZero());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20.,
                'received' => 20.,
            ],
            'sold'    => 40.,
            'shipped' => 5.,
            'locked'  => 5.,
        ]);
        self::assertEquals(new Decimal(20), $assignment->getShippableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20.,
                'received' => 20.,
                'shipped'  => 10.,
                'locked'   => 5.,
            ],
            'sold' => 40.,
        ]);
        self::assertEquals(new Decimal(5), $assignment->getShippableQuantity());
    }

    public function test_getReleasableQuantity(): void
    {
        $assignment = Fixture::stockAssignment();
        self::assertTrue($assignment->getReleasableQuantity()->isZero());

        $assignment = Fixture::stockAssignment([
            'sold' => 20.,
        ]);
        self::assertTrue($assignment->getReleasableQuantity()->isZero()); // No stock unit

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'sold' => 20.,
            ],
            'sold' => 20.,
        ]);
        self::assertEquals(new Decimal(20), $assignment->getReleasableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'sold' => 20.,
            ],
            'sold'    => 20.,
            'shipped' => 5.,
        ]);
        self::assertEquals(new Decimal(15), $assignment->getReleasableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'sold'    => 50.,
                'shipped' => 10.,
            ],
            'sold' => 20.,
        ]);
        self::assertEquals(new Decimal(20), $assignment->getReleasableQuantity());

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'sold' => 50.,
            ],
            'sold'    => 20.,
            'shipped' => 5.,
            'locked'  => 5.,
        ]);
        self::assertEquals(new Decimal(10), $assignment->getReleasableQuantity());
    }

    public function test_isFullyShipped(): void
    {
        $assignment = Fixture::stockAssignment();
        self::assertTrue($assignment->isFullyShipped());

        $assignment->setSoldQuantity(new Decimal(20));
        self::assertFalse($assignment->isFullyShipped());

        $assignment->setShippedQuantity(new Decimal(10));
        self::assertFalse($assignment->isFullyShipped());

        $assignment->setShippedQuantity(new Decimal(20));
        self::assertTrue($assignment->isFullyShipped());
    }

    public function test_isFullyShippable(): void
    {
        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20,
                'received' => 20,
            ],
        ]);

        self::assertTrue($assignment->isFullyShippable());

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20,
                'received' => 20,
            ],
            'sold' => 20,
        ]);
        self::assertTrue($assignment->isFullyShippable());

        $assignment = Fixture::stockAssignment([
            'unit' => [
                'ordered'  => 20,
                'received' => 20,
            ],
            'sold' => 30,
        ]);
        self::assertFalse($assignment->isFullyShippable()); // Limited by the stock unit

        $assignment = Fixture::stockAssignment([
            'unit'    => [
                'ordered'  => 20,
                'received' => 20,
                'shipped'  => 10,
            ],
            'sold'    => 20,
            'shipped' => 10,
        ]);
        self::assertTrue($assignment->isFullyShippable());

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
        self::assertTrue($assignment->isFullyShippable());
    }

    public function test_isEmpty(): void
    {
        $assignment = Fixture::stockAssignment();
        self::assertTrue($assignment->isEmpty());

        $assignment->setSoldQuantity(new Decimal(10));
        self::assertFalse($assignment->isEmpty());
    }
}
