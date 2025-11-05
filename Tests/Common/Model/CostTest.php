<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Cost;
use PHPUnit\Framework\TestCase;

/**
 * Class CostTest
 * @package Ekyna\Component\Commerce\Tests\Common\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CostTest extends TestCase
{
    public function testConstructor(): void
    {
        $cost = new Cost(
            new Decimal(2),
            new Decimal(3),
            new Decimal(4),
            true
        );

        self::assertEquals(2, $cost->getProduct());
        self::assertEquals(3, $cost->getSupply());
        self::assertEquals(4, $cost->getShipment());
        self::assertTrue($cost->isAverage());
    }

    public function testAddProduct(): void
    {
        $immutable = new Cost();

        $result = $immutable->addProduct(new Decimal(10));

        self::assertEquals(0, $immutable->getProduct());
        self::assertEquals(10, $result->getProduct());
    }

    public function testAddSupply(): void
    {
        $immutable = new Cost();

        $result = $immutable->addSupply(new Decimal(10));

        self::assertEquals(0, $immutable->getSupply());
        self::assertEquals(10, $result->getSupply());
    }

    public function testAddShipment(): void
    {
        $immutable = new Cost();

        $result = $immutable->addShipment(new Decimal(10));

        self::assertEquals(0, $immutable->getShipment());
        self::assertEquals(10, $result->getShipment());
    }

    public function testSetAverage(): void
    {
        $immutable = new Cost();

        $result = $immutable->setAverage();

        self::assertFalse($immutable->isAverage());
        self::assertTrue($result->isAverage());
    }

    public function testTotal(): void
    {
        $cost = new Cost(
            new Decimal(10),
            new Decimal(5),
            new Decimal(2)
        );

        self::assertEquals(10, $cost->getTotal(true));
        self::assertEquals(17, $cost->getTotal(false));
    }

    public function testAdd(): void
    {
        $immutable = new Cost(
            new Decimal(10),
            new Decimal(0),
            new Decimal(5),
        );

        $added = new Cost(
            product: new Decimal(2),
            supply: new Decimal(4),
            average: true
        );

        $result = $immutable->add($added);

        self::assertEquals(10, $immutable->getProduct());
        self::assertEquals(0, $immutable->getSupply());
        self::assertEquals(5, $immutable->getShipment());
        self::assertFalse($immutable->isAverage());

        self::assertEquals(2, $added->getProduct());
        self::assertEquals(4, $added->getSupply());
        self::assertEquals(0, $added->getShipment());
        self::assertTrue($added->isAverage());

        self::assertEquals(12, $result->getProduct());
        self::assertEquals(4, $result->getSupply());
        self::assertEquals(5, $result->getShipment());
        self::assertTrue($result->isAverage());
    }

    public function testMultiply(): void
    {
        $immutable = new Cost(
            new Decimal(10),
            new Decimal(2),
            new Decimal(5),
        );

        $result = $immutable->multiply(new Decimal(3));

        self::assertEquals(10, $immutable->getProduct());
        self::assertEquals(2, $immutable->getSupply());
        self::assertEquals(5, $immutable->getShipment());
        self::assertFalse($immutable->isAverage());

        self::assertEquals(30, $result->getProduct());
        self::assertEquals(6, $result->getSupply());
        self::assertEquals(15, $result->getShipment());
        self::assertFalse($result->isAverage());
    }

    public function testDivide(): void
    {
        $immutable = new Cost(
            new Decimal(25),
            new Decimal(10),
            new Decimal(5),
        );

        $result = $immutable->divide(new Decimal(5));

        self::assertEquals(25, $immutable->getProduct());
        self::assertEquals(10, $immutable->getSupply());
        self::assertEquals(5, $immutable->getShipment());
        self::assertFalse($immutable->isAverage());

        self::assertEquals(5, $result->getProduct());
        self::assertEquals(2, $result->getSupply());
        self::assertEquals(1, $result->getShipment());
        self::assertFalse($result->isAverage());
    }

    public function testNegate(): void
    {
        $immutable = new Cost(
            new Decimal(25),
            new Decimal(10),
            new Decimal(5),
        );

        $result = $immutable->negate();

        self::assertEquals(25, $immutable->getProduct());
        self::assertEquals(10, $immutable->getSupply());
        self::assertEquals(5, $immutable->getShipment());
        self::assertFalse($immutable->isAverage());

        self::assertEquals(-25, $result->getProduct());
        self::assertEquals(-10, $result->getSupply());
        self::assertEquals(-5, $result->getShipment());
        self::assertFalse($result->isAverage());
    }

    public function testEquals(): void
    {
        $base = new Cost(
            new Decimal(25),
            new Decimal(10),
            new Decimal(5),
        );

        $equal = new Cost(
            new Decimal(25),
            new Decimal(10),
            new Decimal(5),
        );

        self::assertTrue($base->equals($equal));

        $notEqual = new Cost(
            new Decimal(25),
            new Decimal(10),
            new Decimal(6),
        );

        self::assertFalse($base->equals($notEqual));
    }

    public function testCompareTo(): void
    {
        $base = new Cost(
        new Decimal(25),
        new Decimal(10),
        new Decimal(5),
    );

        $equal = new Cost(
            new Decimal(25),
            new Decimal(10),
            new Decimal(5),
        );

        self::assertEquals(0, $base->compareTo($equal));

        $notEqual = new Cost(
            new Decimal(25),
            new Decimal(10),
            new Decimal(6),
        );

        self::assertEquals(-1, $base->compareTo($notEqual));
    }
}
