<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Cache;

use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCache;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
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
    public function test_add_withoutSubject_throwsException(): void
    {
        $unit = Fixture::stockUnit();

        $this->expectException(LogicException::class);

        $cache = $this->createStockUnitCache();
        $cache->add($unit);

        $this->assertAddedCacheLength($cache, 0);
        $this->assertRemovedCacheLength($cache, 0);
    }

    /**
     * @covers StockUnitCache::add()
     */
    public function test_add(): void
    {
        $unit = Fixture::stockUnit(['subject' => []]);

        $cache = $this->createStockUnitCache();
        $cache->add($unit);

        $this->assertAddedCacheLength($cache, 1);
        $this->assertRemovedCacheLength($cache, 0);
    }

    /**
     * @covers StockUnitCache::isAdded()
     */
    public function test_isAdded(): void
    {
        $unitA = Fixture::stockUnit(['subject' => []]);
        $unitB = Fixture::stockUnit(['subject' => []]);

        $cache = $this->createStockUnitCache();
        $cache->add($unitA);

        $this->assertTrue($cache->isAdded($unitA));
        $this->assertFalse($cache->isAdded($unitB));
    }

    /**
     * @covers StockUnitCache::remove()
     */
    public function test_remove(): void
    {
        $unit = Fixture::stockUnit(['subject' => []]);

        $cache = $this->createStockUnitCache([$unit]);

        $this->assertAddedCacheLength($cache, 1);

        $cache->remove($unit);

        $this->assertAddedCacheLength($cache, 0);
        $this->assertRemovedCacheLength($cache, 1);
    }

    /**
     * @covers StockUnitCache::isRemoved()
     */
    public function test_isRemoved(): void
    {
        $unitA = Fixture::stockUnit(['subject' => []]);
        $unitB = Fixture::stockUnit(['subject' => []]);

        $cache = $this->createStockUnitCache();
        $cache->remove($unitA);

        $this->assertTrue($cache->isRemoved($unitA));
        $this->assertFalse($cache->isRemoved($unitB));
    }

    /**
     * @covers StockUnitCache::findAddedBySubject()
     */
    public function test_findAddedBySubject(): void
    {
        $unitA = Fixture::stockUnit(['subject' => $subjectA = Fixture::subject()]);
        $unitB = Fixture::stockUnit(['subject' => $subjectB = Fixture::subject()]);
        $unitC = Fixture::stockUnit(['subject' => $subjectA]);
        $unitD = Fixture::stockUnit(['subject' => $subjectB]);

        $cache = $this->createStockUnitCache();
        $cache->add($unitA);
        $cache->add($unitB);
        $cache->add($unitC);
        $cache->add($unitD);

        $this->assertEquals([$unitA, $unitC], $cache->findAddedBySubject($subjectA));
        $this->assertEquals([$unitB, $unitD], $cache->findAddedBySubject($subjectB));
    }

    /**
     * @covers StockUnitCache::onEventQueueClose()
     */
    public function test_onEventQueueClose(): void
    {
        $unitA = Fixture::stockUnit(['subject' => []]);
        $unitB = Fixture::stockUnit(['subject' => []]);

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
    private function assertAddedCacheLength(StockUnitCacheInterface $cache, int $expected = 0): void
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
    private function assertRemovedCacheLength(StockUnitCacheInterface $cache, int $expected = 0): void
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
    private function createStockUnitCache(array $stockUnits = []): StockUnitCache
    {
        $cache = new StockUnitCache();

        foreach ($stockUnits as $stockUnit) {
            $cache->add($stockUnit);
        }

        return $cache;
    }
}
