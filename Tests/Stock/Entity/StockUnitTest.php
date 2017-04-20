<?php /** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Stock\Entity;

use Acme\Product\Entity\StockUnit;
use DateTime;
use Ekyna\Component\Commerce\Order\Entity\OrderItemStockAssignment;
use Ekyna\Component\Commerce\Stock\Entity\StockAdjustment;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderItem;
use Ekyna\Component\Commerce\Tests\Fixture;
use PHPUnit\Framework\TestCase;

/**
 * Class StockUnitTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitTest extends TestCase
{
    public function test_geocodes(): void
    {
        $unit = new StockUnit();

        $this->assertSame([], $unit->getGeocodes());
        $this->assertFalse($unit->hasGeocode('Foo'));
        $this->assertFalse($unit->hasGeocode('Bar'));

        $unit->addGeocode('Foo');
        $this->assertSame(['FOO'], $unit->getGeocodes());
        $this->assertTrue($unit->hasGeocode('Foo'));
        $this->assertFalse($unit->hasGeocode('Bar'));

        $unit->addGeocode('BAR');
        $this->assertSame(['FOO', 'BAR'], $unit->getGeocodes());
        $this->assertTrue($unit->hasGeocode('Foo'));
        $this->assertTrue($unit->hasGeocode('Bar'));

        $unit->removeGeocode('Foo');
        $this->assertSame([1 => 'BAR'], $unit->getGeocodes());
        $this->assertFalse($unit->hasGeocode('Foo'));
        $this->assertTrue($unit->hasGeocode('Bar'));
    }

    public function test_setSupplierOrderItem_withItem(): void
    {
        $unit = new StockUnit();

        $this->assertNull($unit->getSupplierOrderItem());

        $itemA = new SupplierOrderItem();
        $unit->setSupplierOrderItem($itemA);
        $this->assertSame($itemA, $unit->getSupplierOrderItem());
        $this->assertSame($unit, $itemA->getStockUnit());

        $order = new SupplierOrder();
        $order
            ->setCurrency(Fixture::currency())
            ->setExchangeRate(1.1)
            ->setExchangeDate($date = new DateTime('2020-01-01'));

        $itemA->setOrder($order);
        $this->assertSame($order, $unit->getSupplierOrder());
        $this->assertSame(1.1, $unit->getExchangeRate());
        $this->assertSame($date, $unit->getExchangeDate());

        $itemB = new SupplierOrderItem();
        $unit->setSupplierOrderItem($itemB);
        $this->assertSame($itemB, $unit->getSupplierOrderItem());
        $this->assertSame($unit, $itemB->getStockUnit());
        $this->assertNull($itemA->getStockUnit());

        $unit->setSupplierOrderItem(null);
        $this->assertNull($unit->getSupplierOrderItem());
        $this->assertNull($itemB->getStockUnit());
    }

    public function test_estimatedDateOfArrival(): void
    {
        $unit = new StockUnit();

        $this->assertNull($unit->getEstimatedDateOfArrival());

        $date = new DateTime('2020-01-01');
        $unit->setEstimatedDateOfArrival($date);
        $this->assertSame($date, $unit->getEstimatedDateOfArrival());

        $unit->setEstimatedDateOfArrival(null);
        $this->assertNull($unit->getEstimatedDateOfArrival());
    }

    public function test_orderedQuantity(): void
    {
        $unit = new StockUnit();

        $this->assertSame(0., $unit->getOrderedQuantity());

        $unit->setOrderedQuantity(10);
        $this->assertSame(10., $unit->getOrderedQuantity());
    }

    public function test_receivedQuantity(): void
    {
        $unit = new StockUnit();

        $this->assertSame(0., $unit->getReceivedQuantity());

        $unit->setReceivedQuantity(10);
        $this->assertSame(10., $unit->getReceivedQuantity());
    }

    public function test_adjustedQuantity(): void
    {
        $unit = new StockUnit();

        $this->assertSame(0., $unit->getAdjustedQuantity());

        $unit->setAdjustedQuantity(10);
        $this->assertSame(10., $unit->getAdjustedQuantity());
    }

    public function test_soldQuantity(): void
    {
        $unit = new StockUnit();

        $this->assertSame(0., $unit->getSoldQuantity());

        $unit->setSoldQuantity(10);
        $this->assertSame(10., $unit->getSoldQuantity());
    }

    public function test_shippedQuantity(): void
    {
        $unit = new StockUnit();

        $this->assertSame(0., $unit->getShippedQuantity());

        $unit->setShippedQuantity(10);
        $this->assertSame(10., $unit->getShippedQuantity());
    }

    public function test_lockedQuantity(): void
    {
        $unit = new StockUnit();

        $this->assertSame(0., $unit->getLockedQuantity());

        $unit->setLockedQuantity(10);
        $this->assertSame(10., $unit->getLockedQuantity());
    }

    public function test_netPriceQuantity(): void
    {
        $unit = new StockUnit();

        $this->assertSame(0., $unit->getNetPrice());

        $unit->setNetPrice(10);
        $this->assertSame(10., $unit->getNetPrice());
    }

    public function test_createdAt(): void
    {
        $unit = new StockUnit();

        $this->assertInstanceOf(DateTime::class, $unit->getCreatedAt());

        $date = new DateTime('2020-01-01');
        $unit->setCreatedAt($date);
        $this->assertSame($date, $unit->getCreatedAt());
    }

    public function test_closedAt(): void
    {
        $unit = new StockUnit();

        $this->assertNull($unit->getClosedAt());

        $date = new DateTime('2020-01-01');
        $unit->setClosedAt($date);
        $this->assertSame($date, $unit->getClosedAt());

        $unit->setClosedAt(null);
        $this->assertNull($unit->getClosedAt());
    }

    public function test_assignments(): void
    {
        $unit = new StockUnit();

        $this->assertEmpty($unit->getStockAssignments());

        $assignmentA = new OrderItemStockAssignment();
        $assignmentB = new OrderItemStockAssignment();

        $unit->addStockAssignment($assignmentA);
        $this->assertContains($assignmentA, $unit->getStockAssignments());
        $this->assertTrue($unit->hasStockAssignment($assignmentA));
        $this->assertFalse($unit->hasStockAssignment($assignmentB));

        $unit->addStockAssignment($assignmentB);
        $this->assertContains($assignmentB, $unit->getStockAssignments());
        $this->assertTrue($unit->hasStockAssignment($assignmentA));
        $this->assertTrue($unit->hasStockAssignment($assignmentB));

        $unit->removeStockAssignment($assignmentA);
        $this->assertNotContains($assignmentA, $unit->getStockAssignments());
        $this->assertFalse($unit->hasStockAssignment($assignmentA));
        $this->assertTrue($unit->hasStockAssignment($assignmentB));
    }

    public function test_adjustments(): void
    {
        $unit = new StockUnit();

        $this->assertEmpty($unit->getStockAdjustments());

        $adjustmentA = new StockAdjustment();
        $adjustmentB = new StockAdjustment();

        $unit->addStockAdjustment($adjustmentA);
        $this->assertContains($adjustmentA, $unit->getStockAdjustments());
        $this->assertTrue($unit->hasStockAdjustment($adjustmentA));
        $this->assertFalse($unit->hasStockAdjustment($adjustmentB));

        $unit->addStockAdjustment($adjustmentB);
        $this->assertContains($adjustmentB, $unit->getStockAdjustments());
        $this->assertTrue($unit->hasStockAdjustment($adjustmentA));
        $this->assertTrue($unit->hasStockAdjustment($adjustmentB));

        $unit->removeStockAdjustment($adjustmentA);
        $this->assertNotContains($adjustmentA, $unit->getStockAdjustments());
        $this->assertFalse($unit->hasStockAdjustment($adjustmentA));
        $this->assertTrue($unit->hasStockAdjustment($adjustmentB));
    }

    public function test_isEmpty(): void
    {
        $unit = new StockUnit();
        $this->assertTrue($unit->isEmpty());

        $unit = new StockUnit();
        $unit->setSupplierOrderItem(new SupplierOrderItem());
        $this->assertFalse($unit->isEmpty());

        $unit = new StockUnit();
        $unit->addStockAssignment(new OrderItemStockAssignment());
        $this->assertFalse($unit->isEmpty());

        $unit = new StockUnit();
        $unit->setOrderedQuantity(1);
        $this->assertFalse($unit->isEmpty());

        $unit = new StockUnit();
        $unit->setAdjustedQuantity(1);
        $this->assertFalse($unit->isEmpty());

        $unit = new StockUnit();
        $unit->setSoldQuantity(1);
        $this->assertFalse($unit->isEmpty());
    }

    public function test_isClosed(): void
    {
        $unit = new StockUnit();
        $this->assertFalse($unit->isClosed());

        $unit->setState(StockUnitStates::STATE_CLOSED);
        $this->assertTrue($unit->isClosed());
    }

    public function test_getReservableQuantity(): void
    {
        $unit = new StockUnit();
        $this->assertSame(INF, $unit->getReservableQuantity());

        $unit = new StockUnit();
        $unit->setOrderedQuantity(10);
        $this->assertSame(10., $unit->getReservableQuantity());

        $unit = new StockUnit();
        $unit->setAdjustedQuantity(10);
        $this->assertSame(10., $unit->getReservableQuantity());

        $unit = new StockUnit();
        $unit->setOrderedQuantity(10);
        $unit->setAdjustedQuantity(10);
        $this->assertSame(20., $unit->getReservableQuantity());

        $unit = new StockUnit();
        $unit->setSoldQuantity(10);
        $this->assertSame(INF, $unit->getReservableQuantity());

        $unit = new StockUnit();
        $unit->setOrderedQuantity(10);
        $unit->setAdjustedQuantity(10);
        $unit->setSoldQuantity(15);
        $this->assertSame(5., $unit->getReservableQuantity());
    }

    public function test_getReleasableQuantity(): void
    {
        $unit = new StockUnit();
        $this->assertSame(0., $unit->getReleasableQuantity());

        $unit = new StockUnit();
        $unit->setSoldQuantity(10);
        $this->assertSame(10., $unit->getReleasableQuantity());

        $unit = new StockUnit();
        $unit->setSoldQuantity(10);
        $unit->setShippedQuantity(10);
        $this->assertSame(0., $unit->getReleasableQuantity());

        $unit = new StockUnit();
        $unit->setSoldQuantity(10);
        $unit->setShippedQuantity(5);
        $this->assertSame(5., $unit->getReleasableQuantity());

        $unit = new StockUnit();
        $unit->setSoldQuantity(10);
        $unit->setLockedQuantity(5);
        $this->assertSame(5., $unit->getReleasableQuantity());

        $unit = new StockUnit();
        $unit->setSoldQuantity(10);
        $unit->setShippedQuantity(4);
        $unit->setLockedQuantity(4);
        $this->assertSame(2., $unit->getReleasableQuantity());
    }

    public function test_getShippableQuantity(): void
    {
        $unit = new StockUnit();
        $this->assertSame(0., $unit->getShippableQuantity());

        $unit = new StockUnit();
        $unit->setReceivedQuantity(10);
        $this->assertSame(10., $unit->getShippableQuantity());

        $unit = new StockUnit();
        $unit->setAdjustedQuantity(10);
        $this->assertSame(10., $unit->getShippableQuantity());

        $unit = new StockUnit();
        $unit->setReceivedQuantity(10);
        $unit->setShippedQuantity(5);
        $this->assertSame(5., $unit->getShippableQuantity());
    }
}
