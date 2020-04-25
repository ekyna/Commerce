<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Manager;

use Ekyna\Component\Commerce\Stock\Manager\StockUnitManager;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\Stock\StockTestCase;

/**
 * Class StockUnitManagerTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockUnitManagerTest extends StockTestCase
{
    /** @var StockUnitManager */
    private $manager;

    protected function setUp(): void
    {
        $this->manager = new StockUnitManager(
            $this->getPersistenceHelperMock(),
            $this->getStockUnitStateResolverMock(),
            $this->getStockUnitCacheMock()
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->manager = null;
    }

    public function test_persistOrRemove(): void
    {
        $unit = Fixture::stockUnit(['item' => []]);

        $this
            ->getStockUnitStateResolverMock()
            ->expects($this->once())
            ->method('resolve')
            ->with($unit);

        $this
            ->getStockUnitCacheMock()
            ->expects($this->once())
            ->method('add')
            ->with($unit);

        $this
            ->getPersistenceHelperMock()
            ->expects($this->once())
            ->method('persistAndRecompute')
            ->with($unit);

        $this->manager->persistOrRemove($unit);
    }

    public function test_persistOrRemove_withEmptyUnit(): void
    {
        $unit = Fixture::stockUnit();

        $this
            ->getStockUnitStateResolverMock()
            ->expects($this->once())
            ->method('resolve')
            ->with($unit);

        $this
            ->getStockUnitCacheMock()
            ->expects($this->once())
            ->method('remove')
            ->with($unit);

        $this
            ->getPersistenceHelperMock()
            ->expects($this->once())
            ->method('remove')
            ->with($unit);

        $this->manager->persistOrRemove($unit);
    }
}
