<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Updater;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdater;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use Ekyna\Component\Commerce\Tests\Stock\BaseStockTestCase;

/**
 * Class StockUnitUpdaterTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitUpdaterTest extends BaseStockTestCase
{
    /**
     * @var StockUnitUpdater
     */
    private $updater;


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->updater = new StockUnitUpdater(
            $this->getPersistenceHelperMock(),
            $this->getStockUnitCacheMock()
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        parent::tearDown();

        unset($this->updater);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withAbsoluteNegativeQuantity_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem());

        $this->expectException(StockLogicException::class);

        $this->updater->updateOrdered($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withRelativeNegativeQuantity_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 9);

        $this->expectException(StockLogicException::class);

        $this->updater->updateOrdered($unit, -10, true);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withAbsoluteQuantityLowerThanReceived_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateOrdered($unit, 9, false);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withRelativeQuantityLowerThanReceived_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateOrdered($unit, -1, true);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withAbsoluteQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectStockUnitWillBePersistedAndAddedToCache($unit);

        $this->updater->updateOrdered($unit, 11, false);

        $this->assertEquals(11, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withRelativeQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectStockUnitWillBePersistedAndAddedToCache($unit);

        $this->updater->updateOrdered($unit, 1, true);

        $this->assertEquals(11, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withZeroAbsoluteQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10);

        $this->expectStockUnitWillBeDeletedAndRemovedFromCache($unit);

        $this->updater->updateOrdered($unit, 0, false);

        $this->assertEquals(0, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withZeroRelativeQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10);

        $this->expectStockUnitWillBeDeletedAndRemovedFromCache($unit);

        $this->updater->updateOrdered($unit, -10, true);

        $this->assertEquals(0, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withAbsoluteNegativeQuantity_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem());

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withRelativeNegativeQuantity_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, -11, true);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withAbsoluteQuantityGreaterThanOrdered_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, 11, false);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withRelativeQuantityGreaterThanOrdered_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, 1, true);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withAbsoluteQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectStockUnitWillBePersistedAndAddedToCache($unit);

        $this->updater->updateReceived($unit, 9, false);

        $this->assertEquals(9, $unit->getReceivedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withRelativeQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->expectStockUnitWillBePersistedAndAddedToCache($unit);

        $this->updater->updateReceived($unit, -1, true);

        $this->assertEquals(9, $unit->getReceivedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteNegativeQuantity_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem());

        $this->expectException(StockLogicException::class);

        $this->updater->updateSold($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withRelativeNegativeQuantity_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 0, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateSold($unit, -11, true);
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantityLowerThanShipped_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 0, 0, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateSold($unit, 9, false);
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantityLowerThanShipped_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 0, 10, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateSold($unit, -1, true);
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10);

        $this->expectStockUnitWillBePersistedAndAddedToCache($unit);

        $this->updater->updateSold($unit, 10, false);

        $this->assertEquals(10, $unit->getSoldQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 0, 10);

        $this->expectStockUnitWillBePersistedAndAddedToCache($unit);

        $this->updater->updateSold($unit, -1, true);

        $this->assertEquals(9, $unit->getSoldQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteNegativeQuantity_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem());

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeNegativeQuantity_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10, 10, 9);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, -10, true);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantityGreaterThanSold_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10, 9);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, 10, false);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantityGreaterThanReceived_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 9, 10);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, 10, false);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantityGreaterThanSold_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 0, 9, 9);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, 1, true);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantityLowerThanReceived_throwsException()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 9, 10, 9);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, 1, true);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10, 10);

        $this->expectStockUnitWillBePersistedAndAddedToCache($unit);

        $this->updater->updateShipped($unit, 10, false);

        $this->assertEquals(10, $unit->getShippedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantity()
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10, 10, 9);

        $this->expectStockUnitWillBePersistedAndAddedToCache($unit);

        $this->updater->updateShipped($unit, 1, true);

        $this->assertEquals(10, $unit->getShippedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateEstimatedDateOfArrival()
     */
    public function test_updateEstimatedDateOfArrival_withDifferentDate()
    {
        $unit = Fixtures::createStockUnit();

        $this
            ->getPersistenceHelperMock()
            ->expects($this->once())
            ->method('persistAndRecompute')
            ->with($unit, true);

        $this->updater->updateEstimatedDateOfArrival($unit, new \DateTime());
    }

    /**
     * @covers StockUnitUpdater::updateEstimatedDateOfArrival()
     */
    public function test_updateEstimatedDateOfArrival_withSameDate()
    {
        $eda = new \DateTime();

        $unit = Fixtures::createStockUnit();
        $unit->setEstimatedDateOfArrival($eda);

        $this
            ->getPersistenceHelperMock()
            ->expects($this->never())
            ->method('persistAndRecompute');

        $this->updater->updateEstimatedDateOfArrival($unit, $eda);
    }

    /**
     * @param $stockUnit
     */
    private function expectStockUnitWillBePersistedAndAddedToCache($stockUnit)
    {
        // Assert unit will be persisted and recomputed
        $this
            ->getPersistenceHelperMock()
            ->expects($this->once())
            ->method('persistAndRecompute')
            ->with($stockUnit, true);

        // Assert unit will be added to the stock unit cache
        $this
            ->getStockUnitCacheMock()
            ->expects($this->once())
            ->method('add')
            ->with($stockUnit);
    }

    /**
     * @param $stockUnit
     */
    private function expectStockUnitWillBeDeletedAndRemovedFromCache($stockUnit)
    {
        // Assert unit will be deleted
        $this
            ->getPersistenceHelperMock()
            ->expects($this->once())
            ->method('remove')
            ->with($stockUnit, true);

        // Assert unit will be remove from the stock unit cache
        $this
            ->getStockUnitCacheMock()
            ->expects($this->once())
            ->method('remove')
            ->with($stockUnit);
    }
}
