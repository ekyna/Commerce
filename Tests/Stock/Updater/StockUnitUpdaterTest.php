<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Updater;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdater;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\Stock\StockTestCase;

/**
 * Class StockUnitUpdaterTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitUpdaterTest extends StockTestCase
{
    /**
     * @var StockUnitUpdater
     */
    private $updater;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->updater = new StockUnitUpdater(
            $this->getPersistenceHelperMock(),
            $this->getStockUnitResolverMock(),
            $this->getStockUnitManagerMock(),
            $this->getStockOverflowHandler()
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->updater = null;
    }

    public function test_updateOrdered(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateOrdered($unit, 11, false);

        $this->assertEquals(11, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withAbsoluteNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit(['item' => []]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateOrdered($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withRelativeNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'    => [],
            'ordered' => 9,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateOrdered($unit, -10, true);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withAbsoluteQuantityLowerThanReceived_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateOrdered($unit, 9, false);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withRelativeQuantityLowerThanReceived_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateOrdered($unit, -1, true);
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withAbsoluteQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateOrdered($unit, 11, false);

        $this->assertEquals(11, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withRelativeQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateOrdered($unit, 1, true);

        $this->assertEquals(11, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withZeroAbsoluteQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'    => [],
            'ordered' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateOrdered($unit, 0, false);

        $this->assertEquals(0, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateOrdered()
     */
    public function test_updateOrdered_withZeroRelativeQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'    => [],
            'ordered' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateOrdered($unit, -10, true);

        $this->assertEquals(0, $unit->getOrderedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withAbsoluteNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit(['item' => []]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withRelativeNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, -11, true);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withAbsoluteQuantityGreaterThanOrdered_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, 11, false);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withRelativeQuantityGreaterThanOrdered_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, 1, true);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withAbsoluteQuantityLowerThanShippedLockedAdjusted_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'adjusted' => 10,
            'sold'     => 5,
            'shipped'  => 5,
            'locked'   => 5,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, 9, false);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withRelativeQuantityLowerThanShippedLockedAdjusted_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'adjusted' => 10,
            'sold'     => 5,
            'shipped'  => 5,
            'locked'   => 5,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateReceived($unit, -1, true);
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withAbsoluteQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateReceived($unit, 9, false);

        $this->assertEquals(9, $unit->getReceivedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateReceived()
     */
    public function test_updateReceived_withRelativeQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateReceived($unit, -1, true);

        $this->assertEquals(9, $unit->getReceivedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateAdjusted()
     */
    public function test_updateAdjusted_withAbsoluteNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit(['item' => []]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateAdjusted($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateAdjusted()
     */
    public function test_updateAdjusted_withRelativeNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'adjusted' => 10,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateAdjusted($unit, -11, true);
    }

    /**
     * @covers StockUnitUpdater::updateAdjusted()
     */
    public function test_updateAdjusted_withAbsoluteQuantityLowerThanShippedLockedReceived_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'adjusted' => 10,
            'shipped'  => 5,
            'locked'   => 5,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateAdjusted($unit, 9, false);
    }

    /**
     * @covers StockUnitUpdater::updateAdjusted()
     */
    public function test_updateAdjusted_withRelativeQuantityLowerThanShippedLockedReceived_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'adjusted' => 10,
            'shipped'  => 5,
            'locked'   => 5,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateAdjusted($unit, -1, true);
    }

    /**
     * @covers StockUnitUpdater::updateAdjusted()
     */
    public function test_updateAdjusted_withAbsoluteQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'adjusted' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateAdjusted($unit, 9, false);

        $this->assertEquals(9, $unit->getAdjustedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateAdjusted()
     */
    public function test_updateAdjusted_withRelativeQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'adjusted' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateAdjusted($unit, -1, true);

        $this->assertEquals(9, $unit->getAdjustedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit(['item' => []]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateSold($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withRelativeNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item' => [],
            'sold' => 10,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateSold($unit, -11, true);
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantityLowerThanShippedLocked_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
            'sold'     => 10,
            'shipped'  => 5,
            'locked'   => 5,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateSold($unit, 9, false);
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantityLowerThanShippedLocked_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
            'sold'     => 10,
            'shipped'  => 5,
            'locked'   => 5,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateSold($unit, -1, true);
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'    => [],
            'ordered' => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateSold($unit, 10, false);

        $this->assertEquals(10, $unit->getSoldQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'    => [],
            'ordered' => 10,
            'sold'    => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateSold($unit, -1, true);

        $this->assertEquals(9, $unit->getSoldQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit(['item' => []]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, -1, false);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeNegativeQuantity_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
            'sold'     => 10,
            'shipped'  => 9,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, -10, true);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantityGreaterThanSold_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
            'sold'     => 9,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, 10, false);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantityGreaterThanReceivedAdjustedLocked_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 5,
            'adjusted' => 5,
            'sold'     => 10,
            'locked'   => 5,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, 11, false);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantityGreaterThanSold_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'    => [],
            'ordered' => 10,
            'sold'    => 9,
            'shipped' => 9,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, 1, true);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantityLowerThanReceived_throwsException(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 9,
            'sold'     => 10,
            'shipped'  => 9,
        ]);

        $this->expectException(StockLogicException::class);

        $this->updater->updateShipped($unit, 1, true);
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
            'sold'     => 10,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateShipped($unit, 10, false);

        $this->assertEquals(10, $unit->getShippedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantity(): void
    {
        $unit = Fixture::stockUnit([
            'item'     => [],
            'ordered'  => 10,
            'received' => 10,
            'sold'     => 10,
            'shipped'  => 9,
        ]);

        $this->expectStockUnitWillBePersistedOrRemoved($unit);

        $this->updater->updateShipped($unit, 1, true);

        $this->assertEquals(10, $unit->getShippedQuantity());
    }

    /**
     * @covers StockUnitUpdater::updateEstimatedDateOfArrival()
     */
    public function test_updateEstimatedDateOfArrival_withDifferentDate(): void
    {
        $unit = Fixture::stockUnit();

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
    public function test_updateEstimatedDateOfArrival_withSameDate(): void
    {
        $eda = new \DateTime();

        $unit = Fixture::stockUnit();
        $unit->setEstimatedDateOfArrival($eda);

        $this
            ->getPersistenceHelperMock()
            ->expects($this->never())
            ->method('persistAndRecompute');

        $this->updater->updateEstimatedDateOfArrival($unit, $eda);
    }

    /**
     * @param StockUnitInterface $stockUnit
     */
    private function expectStockUnitWillBePersistedOrRemoved(StockUnitInterface $stockUnit): void
    {
        $this
            ->getStockUnitManagerMock()
            ->expects($this->once())
            ->method('persistOrRemove')
            ->with($stockUnit);
    }
}
