<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Updater;

use Ekyna\Component\Commerce\Stock\Updater\StockAssignmentUpdater;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use Ekyna\Component\Commerce\Tests\Stock\BaseStockTestCase;

/**
 * Class StockAssignmentUpdaterTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentUpdaterTest extends BaseStockTestCase
{
    /**
     * @var StockAssignmentUpdater
     */
    private $updater;


    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->updater = new StockAssignmentUpdater(
            $this->getPersistenceHelperMock(),
            $this->getStockUnitUpdaterMock()
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
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantityLowerThanZero_returnsZero()
    {
        $unit = Fixtures::createStockUnit(null, null, 0, 0, 0, 0);
        $assignment = Fixtures::createStockAssignment($unit);

        $this->assertAssignmentWontBePersisted();
        $this->assertStockUnitSoldQuantityWontBeUpdated();

        $return = $this->updater->updateSold($assignment, -1, true);

        $this->assertEquals(0, $return);
        $this->assertEquals(0, $assignment->getSoldQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantityLowerThanZero_returnsZero()
    {
        $unit = Fixtures::createStockUnit(null, null, 0, 0, 0, 0);
        $assignment = Fixtures::createStockAssignment($unit);

        $this->assertAssignmentWontBePersisted();
        $this->assertStockUnitSoldQuantityWontBeUpdated();

        $return = $this->updater->updateSold($assignment, -1, false);

        $this->assertEquals(0, $return);
        $this->assertEquals(0, $assignment->getSoldQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantityLowerThanShipped_returnsZero()
    {
        $unit = Fixtures::createStockUnit(null, null, 5, 5, 5, 5);
        $assignment = Fixtures::createStockAssignment($unit, null, 5, 5);

        $this->assertAssignmentWontBePersisted();
        $this->assertStockUnitSoldQuantityWontBeUpdated();

        $return = $this->updater->updateSold($assignment, -1, true);

        $this->assertEquals(0, $return);
        $this->assertEquals(5, $assignment->getSoldQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantityLowerThanShipped_returnsZero()
    {
        $unit = Fixtures::createStockUnit(null, null, 5, 5, 5, 5);
        $assignment = Fixtures::createStockAssignment($unit, null, 5, 5);

        $this->assertAssignmentWontBePersisted();
        $this->assertStockUnitSoldQuantityWontBeUpdated();

        $return = $this->updater->updateSold($assignment, 4, false);

        $this->assertEquals(0, $return);
        $this->assertEquals(5, $assignment->getSoldQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantityGreaterThanOrdered_returnsLimit()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 0, 5, 0);
        $assignment = Fixtures::createStockAssignment($unit, null, 5, 0);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitSoldQuantityWillBeUpdated($unit, 5);

        $return = $this->updater->updateSold($assignment, 6, true);

        $this->assertEquals(5, $return);
        $this->assertEquals(10, $assignment->getSoldQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantityGreaterThanOrdered_returnsLimit()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 0, 5, 0);
        $assignment = Fixtures::createStockAssignment($unit, null, 5, 0);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitSoldQuantityWillBeUpdated($unit, 5);

        $return = $this->updater->updateSold($assignment, 11, false);

        $this->assertEquals(5, $return);
        $this->assertEquals(10, $assignment->getSoldQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantityInbound_returnsQuantity()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 0, 5, 0);
        $assignment = Fixtures::createStockAssignment($unit, null, 5, 0);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitSoldQuantityWillBeUpdated($unit, 4);

        $return = $this->updater->updateSold($assignment, 4, true);

        $this->assertEquals(4, $return);
        $this->assertEquals(9, $assignment->getSoldQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantityInbound_returnsQuantity()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 0, 5, 0);
        $assignment = Fixtures::createStockAssignment($unit, null, 5, 0);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitSoldQuantityWillBeUpdated($unit, 4);

        $return = $this->updater->updateSold($assignment, 9, false);

        $this->assertEquals(4, $return);
        $this->assertEquals(9, $assignment->getSoldQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withRelativeQuantityToZero_assignmentRemoved()
    {
        $unit = Fixtures::createStockUnit(null, null, 0, 0, 5, 0);
        $assignment = Fixtures::createStockAssignment($unit, Fixtures::createOrderItem(), 5, 0);

        $this->assertAssignmentWillBeDeleted($assignment);
        $this->assertStockUnitSoldQuantityWillBeUpdated($unit, -5);

        $return = $this->updater->updateSold($assignment, -5, true);

        $this->assertEquals(-5, $return);
    }

    /**
     * @covers StockAssignmentUpdater::updateSold()
     */
    public function test_updateSold_withAbsoluteQuantityToZero_assignmentRemoved()
    {
        $unit = Fixtures::createStockUnit(null, null, 0, 0, 5, 0);
        $assignment = Fixtures::createStockAssignment($unit, Fixtures::createOrderItem(), 5, 0);

        $this->assertAssignmentWillBeDeleted($assignment);
        $this->assertStockUnitSoldQuantityWillBeUpdated($unit, -5);

        $return = $this->updater->updateSold($assignment, 0, false);

        $this->assertEquals(-5, $return);
    }

    /**
     * @covers StockAssignmentUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantityLowerThanZero_returnsZero()
    {
        $unit = Fixtures::createStockUnit(null, null, 0, 0, 0, 0);
        $assignment = Fixtures::createStockAssignment($unit);

        $this->assertAssignmentWontBePersisted();
        $this->assertStockUnitShippedQuantityWontBeUpdated();

        $return = $this->updater->updateShipped($assignment, -1, true);

        $this->assertEquals(0, $return);
        $this->assertEquals(0, $assignment->getShippedQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantityLowerThanZero_returnsZero()
    {
        $unit = Fixtures::createStockUnit(null, null, 0, 0, 0, 0);
        $assignment = Fixtures::createStockAssignment($unit);

        $this->assertAssignmentWontBePersisted();
        $this->assertStockUnitShippedQuantityWontBeUpdated();

        $return = $this->updater->updateShipped($assignment, -1, false);

        $this->assertEquals(0, $return);
        $this->assertEquals(0, $assignment->getShippedQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantityGreaterThanReceived_returnsLimit()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 9, 10, 5);
        $assignment = Fixtures::createStockAssignment($unit, null, 10, 5);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitShippedQuantityWillBeUpdated($unit, 4);

        $return = $this->updater->updateShipped($assignment, 5, true);

        $this->assertEquals(4, $return);
        $this->assertEquals(9, $assignment->getShippedQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantityGreaterThanReceived_returnsLimit()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 9, 10, 5);
        $assignment = Fixtures::createStockAssignment($unit, null, 10, 5);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitShippedQuantityWillBeUpdated($unit, 4);

        $return = $this->updater->updateShipped($assignment, 10, false);

        $this->assertEquals(4, $return);
        $this->assertEquals(9, $assignment->getShippedQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantityGreaterThanSold_returnsLimit()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 10, 9, 5);
        $assignment = Fixtures::createStockAssignment($unit, null, 9, 5);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitShippedQuantityWillBeUpdated($unit, 4);

        $return = $this->updater->updateShipped($assignment, 5, true);

        $this->assertEquals(4, $return);
        $this->assertEquals(9, $assignment->getShippedQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantityGreaterThanSold_returnsLimit()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 10, 9, 5);
        $assignment = Fixtures::createStockAssignment($unit, null, 9, 5);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitShippedQuantityWillBeUpdated($unit, 4);

        $return = $this->updater->updateShipped($assignment, 10, false);

        $this->assertEquals(4, $return);
        $this->assertEquals(9, $assignment->getShippedQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateShipped()
     */
    public function test_updateShipped_withRelativeQuantityInbound_returnsQuantity()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 10, 10, 5);
        $assignment = Fixtures::createStockAssignment($unit, null, 10, 5);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitShippedQuantityWillBeUpdated($unit, 4);

        $return = $this->updater->updateShipped($assignment, 4, true);

        $this->assertEquals(4, $return);
        $this->assertEquals(9, $assignment->getShippedQuantity());
    }

    /**
     * @covers StockAssignmentUpdater::updateShipped()
     */
    public function test_updateShipped_withAbsoluteQuantityInbound_returnsQuantity()
    {
        $unit = Fixtures::createStockUnit(null, null, 10, 10, 10, 5);
        $assignment = Fixtures::createStockAssignment($unit, null, 10, 5);

        $this->assertAssignmentWillBePersisted($assignment);
        $this->assertStockUnitShippedQuantityWillBeUpdated($unit, 4);

        $return = $this->updater->updateShipped($assignment, 9, false);

        $this->assertEquals(4, $return);
        $this->assertEquals(9, $assignment->getShippedQuantity());
    }

    /**
     * Asserts that the assignment won't be persisted.
     */
    private function assertAssignmentWontBePersisted()
    {
        $this
            ->getPersistenceHelperMock()
            ->expects($this->never())
            ->method('persistAndRecompute');
    }

    /**
     * Asserts that the assignment will be persisted.
     *
     * @param \Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface $assignment
     */
    private function assertAssignmentWillBePersisted($assignment)
    {
        $this
            ->getPersistenceHelperMock()
            ->expects($this->once())
            ->method('persistAndRecompute')
            ->with($assignment, false);
    }

    /**
     * Asserts that the assignment will be deleted.
     *
     * @param \Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface $assignment
     */
    private function assertAssignmentWillBeDeleted($assignment)
    {
        $this
            ->getPersistenceHelperMock()
            ->expects($this->once())
            ->method('remove')
            ->with($assignment, false);
    }

    /**
     * Asserts that the stock unit sold quantity will be updated.
     *
     * @param \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface $stockUnit
     * @param float                                                    $quantity
     */
    private function assertStockUnitSoldQuantityWillBeUpdated($stockUnit, $quantity)
    {
        $this
            ->getStockUnitUpdaterMock()
            ->expects($this->once())
            ->method('updateSold')
            ->with($stockUnit, $quantity, true);
    }

    /**
     * Asserts that the stock unit sold quantity won't be updated.
     */
    private function assertStockUnitSoldQuantityWontBeUpdated()
    {
        $this
            ->getStockUnitUpdaterMock()
            ->expects($this->never())
            ->method('updateSold');
    }

    /**
     * Asserts that the stock unit shipped quantity will be updated.
     *
     * @param \Ekyna\Component\Commerce\Stock\Model\StockUnitInterface $stockUnit
     * @param float                                                    $quantity
     */
    private function assertStockUnitShippedQuantityWillBeUpdated($stockUnit, $quantity)
    {
        $this
            ->getStockUnitUpdaterMock()
            ->expects($this->once())
            ->method('updateShipped')
            ->with($stockUnit, $quantity, true);
    }

    /**
     * Asserts that the stock unit shipped quantity won't be updated.
     */
    private function assertStockUnitShippedQuantityWontBeUpdated()
    {
        $this
            ->getStockUnitUpdaterMock()
            ->expects($this->never())
            ->method('updateShipped');
    }
}
