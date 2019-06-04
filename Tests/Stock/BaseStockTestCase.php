<?php

namespace Ekyna\Component\Commerce\Tests\Stock;

use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcherInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandlerInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Tests\BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class BaseStockTestCase
 * @package Ekyna\Component\Commerce\Tests\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BaseStockTestCase extends BaseTestCase
{
    /**
     * @var StockUnitResolverInterface|MockObject
     */
    private $stockUnitResolver;

    /**
     * @var StockUnitCacheInterface|MockObject
     */
    private $stockUnitCache;

    /**
     * @var StockUnitManagerInterface|MockObject
     */
    private $stockUnitManager;

    /**
     * @var StockUnitUpdaterInterface|MockObject
     */
    private $stockUnitUpdater;

    /**
     * @var StockAssignmentDispatcherInterface|MockObject
     */
    private $stockAssignmentDispatcher;

    /**
     * @var OverflowHandlerInterface|MockObject
     */
    private $stockOverflowHandler;

    /**
     * Returns the unit resolver mock.
     *
     * @return StockUnitResolverInterface|MockObject
     */
    protected function getStockUnitResolverMock(): StockUnitResolverInterface
    {
        if (null !== $this->stockUnitResolver) {
            return $this->stockUnitResolver;
        }

        return $this->stockUnitResolver = $this->createMock(StockUnitResolverInterface::class);
    }

    /**
     * Returns the stock unit cache mock.
     *
     * @return StockUnitCacheInterface|MockObject
     */
    protected function getStockUnitCacheMock(): StockUnitCacheInterface
    {
        if (null !== $this->stockUnitCache) {
            return $this->stockUnitCache;
        }

        return $this->stockUnitCache = $this->createMock(StockUnitCacheInterface::class);
    }

    /**
     * Returns the stock unit manager mock.
     *
     * @return StockUnitManagerInterface|MockObject
     */
    protected function getStockUnitManagerMock(): StockUnitManagerInterface
    {
        if (null !== $this->stockUnitManager) {
            return $this->stockUnitManager;
        }

        return $this->stockUnitManager = $this->createMock(StockUnitManagerInterface::class);
    }

    /**
     * Returns the stock unit updater mock.
     *
     * @return StockUnitUpdaterInterface|MockObject
     */
    protected function getStockUnitUpdaterMock(): StockUnitUpdaterInterface
    {
        if (null !== $this->stockUnitUpdater) {
            return $this->stockUnitUpdater;
        }

        return $this->stockUnitUpdater = $this->createMock(StockUnitUpdaterInterface::class);
    }

    /**
     * Returns the stock assignment dispatcher mock.
     *
     * @return StockAssignmentDispatcherInterface|MockObject
     */
    protected function getStockAssignmentDispatcherMock(): StockAssignmentDispatcherInterface
    {
        if (null !== $this->stockAssignmentDispatcher) {
            return $this->stockAssignmentDispatcher;
        }

        return $this->stockAssignmentDispatcher = $this->createMock(StockAssignmentDispatcherInterface::class);
    }

    /**
     * Returns the stock unit overflow handler mock.
     *
     * @return OverflowHandlerInterface|MockObject
     */
    protected function getStockOverflowHandler(): OverflowHandlerInterface
    {
        if (null !== $this->stockOverflowHandler) {
            return $this->stockOverflowHandler;
        }

        return $this->stockOverflowHandler = $this->createMock(OverflowHandlerInterface::class);
    }
}
