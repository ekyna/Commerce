<?php

namespace Ekyna\Component\Commerce\Tests\Stock;

use Ekyna\Component\Commerce\Stock\Cache\StockAssignmentCacheInterface;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcherInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandlerInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class BaseStockTestCase
 * @package Ekyna\Component\Commerce\Tests\Stock
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockTestCase extends TestCase
{
    /**
     * @var StockUnitResolverInterface|MockObject
     */
    private $stockUnitResolver;

    /**
     * @var StockUnitStateResolverInterface|MockObject
     */
    private $stockUnitStateResolver;

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
     * @var StockAssignmentManagerInterface|MockObject
     */
    private $stockAssignmentManager;

    /**
     * @var StockAssignmentManagerInterface|MockObject
     */
    private $stockAssignmentCache;

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
     * Returns the unit state resolver mock.
     *
     * @return StockUnitStateResolverInterface|MockObject
     */
    protected function getStockUnitStateResolverMock(): StockUnitStateResolverInterface
    {
        if (null !== $this->stockUnitStateResolver) {
            return $this->stockUnitStateResolver;
        }

        return $this->stockUnitStateResolver = $this->createMock(StockUnitStateResolverInterface::class);
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
     * Returns the stock assignment manager mock.
     *
     * @return StockAssignmentManagerInterface|MockObject
     */
    protected function getStockAssignmentManagerMock(): StockAssignmentManagerInterface
    {
        if (null !== $this->stockAssignmentManager) {
            return $this->stockAssignmentManager;
        }

        return $this->stockAssignmentManager = $this->createMock(StockAssignmentManagerInterface::class);
    }

    /**
     * Returns the stock assignment cache mock.
     *
     * @return StockAssignmentCacheInterface|MockObject
     */
    protected function getStockAssignmentCacheMock(): StockAssignmentCacheInterface
    {
        if (null !== $this->stockAssignmentCache) {
            return $this->stockAssignmentCache;
        }

        return $this->stockAssignmentCache = $this->createMock(StockAssignmentCacheInterface::class);
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
