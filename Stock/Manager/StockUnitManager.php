<?php

namespace Ekyna\Component\Commerce\Stock\Manager;

use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitManager
 * @package Ekyna\Component\Commerce\Stock\Manager
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitManager implements StockUnitManagerInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitStateResolverInterface
     */
    protected $stateResolver;

    /**
     * @var StockUnitCacheInterface
     */
    protected $unitCache;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface      $persistenceHelper
     * @param StockUnitStateResolverInterface $stateResolver
     * @param StockUnitCacheInterface         $unitCache
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitStateResolverInterface $stateResolver,
        StockUnitCacheInterface $unitCache
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stateResolver = $stateResolver;
        $this->unitCache = $unitCache;
    }

    /**
     * @inheritdoc
     */
    public function persistOrRemove(StockUnitInterface $stockUnit)
    {
        // Resolve the stock unit's state
        $this->stateResolver->resolve($stockUnit);

        // If empty, remove without scheduling event
        if ($stockUnit->isEmpty()) {
            // Remove the stock unit from the cache
            $this->unitCache->remove($stockUnit);

            // Remove and schedule event
            $this->persistenceHelper->remove($stockUnit, true);

            return;
        }

        // Adds the stock unit to the cache
        $this->unitCache->add($stockUnit);

        // Persist and schedule event
        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }
}