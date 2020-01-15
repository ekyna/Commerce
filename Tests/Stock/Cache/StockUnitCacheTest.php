<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Cache;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCache;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use Ekyna\Component\Commerce\Tests\Stock\StockTestCase;

/**
 * Class StockUnitCacheTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Cache
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitCacheTest extends StockTestCase
{
    /**
     * @covers StockUnitCache::add()
     */
    public function test_add_withoutSubject_throwsException()
    {
        $unit = Fixtures::createStockUnit();

        $this->expectException(LogicException::class);

        $cache = $this->createStockUnitCache();
        $cache->add($unit);

        $this->assertAddedCacheLength($cache, 0);
        $this->assertRemovedCacheLength($cache, 0);
    }

    /**
     * @covers StockUnitCache::add()
     */
    public function test_add()
    {
        // Adding a stock unit with subject should just work
        $unit = Fixtures::createStockUnit(Fixtures::createSubject());

        $cache = $this->createStockUnitCache();
        $cache->add($unit);

        $this->assertAddedCacheLength($cache, 1);
        $this->assertRemovedCacheLength($cache, 0);
    }

    /**
     * @covers StockUnitCache::remove()
     */
    public function test_remove()
    {
        $unit = Fixtures::createStockUnit(Fixtures::createSubject());

        $cache = $this->createStockUnitCache([$unit]);

        $this->assertAddedCacheLength($cache, 1);

        $cache->remove($unit);

        $this->assertAddedCacheLength($cache, 0);
        $this->assertRemovedCacheLength($cache, 1);
    }

    /**
     * @covers StockUnitCache::findNewBySubject()
     */
    public function test_findNewBySubject()
    {
        $subject = Fixtures::createSubject();

        $newUnit = Fixtures::createStockUnit($subject);
        $pendingUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10);
        $readyUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 5, 5);
        $closedUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 10, 10);

        $cache = $this->createStockUnitCache([$newUnit, $pendingUnit, $readyUnit, $closedUnit]);

        $found = array_values($cache->findNewBySubject($subject));

        $this->assertEquals([$newUnit], $found);
    }

    /**
     * @covers StockUnitCache::findPendingBySubject()
     */
    public function test_findPendingBySubject()
    {
        $subject = Fixtures::createSubject();

        $newUnit = Fixtures::createStockUnit($subject);
        $pendingUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10);
        $readyUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 5, 5);
        $closedUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 10, 10);

        $cache = $this->createStockUnitCache([$newUnit, $pendingUnit, $readyUnit, $closedUnit]);

        $found = array_values($cache->findPendingBySubject($subject));

        $this->assertEquals([$pendingUnit], $found);
    }

    /**
     * @covers StockUnitCache::findPendingOrReadyBySubject()
     */
    public function test_findPendingOrReadyBySubject()
    {
        $subject = Fixtures::createSubject();

        $newUnit = Fixtures::createStockUnit($subject);
        $pendingUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10);
        $readyUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 5, 5);
        $closedUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 10, 10);

        $cache = $this->createStockUnitCache([$newUnit, $pendingUnit, $readyUnit, $closedUnit]);

        $found = array_values($cache->findPendingOrReadyBySubject($subject));

        $this->assertEquals([$pendingUnit, $readyUnit], $found);
    }

    /**
     * @covers StockUnitCache::findNotClosedBySubject()
     */
    public function test_findNotClosedBySubject()
    {
        $subject = Fixtures::createSubject();

        $newUnit = Fixtures::createStockUnit($subject);
        $pendingUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10);
        $readyUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 5, 5);
        $closedUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 10, 10);

        $cache = $this->createStockUnitCache([$newUnit, $pendingUnit, $readyUnit, $closedUnit]);

        $found = array_values($cache->findNotClosedBySubject($subject));

        $this->assertEquals([$newUnit, $pendingUnit, $readyUnit], $found);
    }

    /**
     * @covers StockUnitCache::findAssignableBySubject()
     */
    public function test_findAssignableBySubject()
    {
        $subject = Fixtures::createSubject();

        $newUnit = Fixtures::createStockUnit($subject);
        $pendingUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10);
        $readyUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 5, 5);
        $closedUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 10, 10);

        $cache = $this->createStockUnitCache([$newUnit, $pendingUnit, $readyUnit, $closedUnit]);

        $found = array_values($cache->findAssignableBySubject($subject));

        $this->assertEquals([$newUnit, $pendingUnit, $readyUnit], $found);
    }

    /**
     * @covers StockUnitCache::findLinkableBySubject()
     */
    public function test_findLinkableBySubject()
    {
        $subject = Fixtures::createSubject();

        $newUnit = Fixtures::createStockUnit($subject);
        $pendingUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10);
        $readyUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10);
        $closedUnit = Fixtures::createStockUnit($subject, Fixtures::createSupplierOrderItem(), 10, 10, 10, 10);

        $cache = $this->createStockUnitCache([$newUnit, $pendingUnit, $readyUnit, $closedUnit]);

        $found = array_values($cache->findLinkableBySubject($subject));

        $this->assertEquals([$newUnit], $found);
    }

    /**
     * @covers StockUnitCache::onEventQueueClose()
     */
    public function test_onEventQueueClose()
    {
        $unitA = Fixtures::createStockUnit(Fixtures::createSubject());
        $unitB = Fixtures::createStockUnit(Fixtures::createSubject());

        $cache = $this->createStockUnitCache([$unitA, $unitB]);

        $cache->onEventQueueClose();

        $this->assertAddedCacheLength($cache, 0);
    }

    /**
     * Asserts that the cache contains <expected> added stock units.
     *
     * @param StockUnitCacheInterface $cache
     * @param int                     $expected
     */
    private function assertAddedCacheLength(StockUnitCacheInterface $cache, $expected = 0)
    {
        $rc = new \ReflectionClass(StockUnitCache::class);
        $rp = $rc->getProperty('addedUnits');
        $rp->setAccessible(true);

        $this->assertCount($expected, $rp->getValue($cache));
    }

    /**
     * Asserts that the cache contains <expected> removed stock units.
     *
     * @param StockUnitCacheInterface $cache
     * @param int                     $expected
     */
    private function assertRemovedCacheLength(StockUnitCacheInterface $cache, $expected = 0)
    {
        $rc = new \ReflectionClass(StockUnitCache::class);
        $rp = $rc->getProperty('removedUnits');
        $rp->setAccessible(true);

        $this->assertCount($expected, $rp->getValue($cache));
    }

    /**
     * Creates a stock unit cache.
     *
     * @param array $stockUnits
     *
     * @return StockUnitCache
     */
    private function createStockUnitCache(array $stockUnits = [])
    {
        $cache = new StockUnitCache();

        foreach ($stockUnits as $stockUnit) {
            $cache->add($stockUnit);
        }

        return $cache;
    }
}
