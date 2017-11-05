<?php

namespace Ekyna\Component\Commerce\Tests\Stock;

use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Tests\BaseTestCase;

/**
 * Class BaseStockTestCase
 * @package Ekyna\Component\Commerce\Tests\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BaseStockTestCase extends BaseTestCase
{
    /**
     * @var StockUnitResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockUnitResolver;

    /**
     * @var StockUnitCacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockUnitCache;

    /**
     * @var StockUnitUpdaterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stockUnitUpdater;


    /**
     * Returns the unit resolver mock.
     *
     * @return StockUnitResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStockUnitResolverMock()
    {
        if (null !== $this->stockUnitResolver) {
            return $this->stockUnitResolver;
        }

        return $this->stockUnitResolver = $this->createMock(StockUnitResolverInterface::class);
    }

    /**
     * Returns the stock unit cache mock.
     *
     * @return StockUnitCacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStockUnitCacheMock()
    {
        if (null !== $this->stockUnitCache) {
            return $this->stockUnitCache;
        }

        return $this->stockUnitCache = $this->createMock(StockUnitCacheInterface::class);
    }

    /**
     * Returns the stock unit updater mock.
     *
     * @return StockUnitUpdaterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStockUnitUpdaterMock()
    {
        if (null !== $this->stockUnitUpdater) {
            return $this->stockUnitUpdater;
        }

        return $this->stockUnitUpdater = $this->createMock(StockUnitUpdaterInterface::class);
    }
}
