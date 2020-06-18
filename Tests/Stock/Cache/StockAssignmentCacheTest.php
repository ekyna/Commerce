<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Cache;

use Ekyna\Component\Commerce\Stock\Cache\StockAssignmentCache;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\Stock\StockTestCase;

/**
 * Class StockAssignmentCacheTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Cache
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentCacheTest extends StockTestCase
{
    public function test_remove(): void
    {
        $cache = $this->createStockAssignmentCache();

        $this->assertRemovedCacheLength($cache, 0);

        $assignment = Fixture::stockAssignment(['unit' => [], 'item' => []]);

        $cache->addRemoved($assignment);

        $this->assertRemovedCacheLength($cache, 1);
    }

    public function test_findRemoved(): void
    {
        $expected = Fixture::stockAssignment([
            'unit' => $unit = Fixture::stockUnit(),
            'item' => $item = Fixture::orderItem(),
        ]);

        $cache = $this->createStockAssignmentCache();

        $cache->addRemoved(Fixture::stockAssignment(['unit' => [], 'item' => []]));
        $cache->addRemoved(Fixture::stockAssignment(['unit' => [], 'item' => []]));
        $cache->addRemoved($expected);
        $cache->addRemoved(Fixture::stockAssignment(['unit' => [], 'item' => []]));
        $cache->addRemoved(Fixture::stockAssignment(['unit' => [], 'item' => []]));

        $this->assertSame($expected, $cache->findRemoved($unit, $item));
    }

    public function test_flush(): void
    {
        $expected = [
            Fixture::stockAssignment(['unit' => [], 'item' => []]),
            Fixture::stockAssignment(['unit' => [], 'item' => []]),
            Fixture::stockAssignment(['unit' => [], 'item' => []]),
        ];

        $cache = $this->createStockAssignmentCache($expected);

        $this->assertSame($expected, array_values($cache->flush()));

        $this->assertRemovedCacheLength($cache, 0);
    }

    /**
     * Asserts that the cache contains <expected> removed stock assignments.
     *
     * @param StockAssignmentCache $cache
     * @param int                  $expected
     */
    private function assertRemovedCacheLength(StockAssignmentCache $cache, int $expected = 0): void
    {
        $rc = new \ReflectionClass(StockAssignmentCache::class);
        $rp = $rc->getProperty('removedAssignments');
        $rp->setAccessible(true);

        $this->assertCount($expected, $rp->getValue($cache));
    }

    /**
     * Creates a stock assignment cache.
     *
     * @param array $removedAssignments
     *
     * @return StockAssignmentCache
     */
    private function createStockAssignmentCache(array $removedAssignments = []): StockAssignmentCache
    {
        $cache = new StockAssignmentCache();

        foreach ($removedAssignments as $assignment) {
            $cache->addRemoved($assignment);
        }

        return $cache;
    }
}
