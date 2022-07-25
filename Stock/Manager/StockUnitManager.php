<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Manager;

use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Resource\Persistence\PersistenceEventQueueInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitManager
 * @package Ekyna\Component\Commerce\Stock\Manager
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitManager implements StockUnitManagerInterface
{
    public function __construct(
        protected readonly PersistenceHelperInterface      $persistenceHelper,
        protected readonly StockUnitStateResolverInterface $stateResolver,
        protected readonly StockUnitCacheInterface         $unitCache
    ) {
    }

    /**
     * @inheritDoc
     */
    public function persistOrRemove(StockUnitInterface $stockUnit): void
    {
        // Resolve the stock unit's state
        $this->stateResolver->resolve($stockUnit);

        // If empty, remove and schedule event
        if ($stockUnit->isEmpty()) {
            // Remove the stock unit from the cache
            $this->unitCache->remove($stockUnit);

            // This will raise an error if an update event is already scheduled for this stock unit
            $this->persistenceHelper->clearEvent($stockUnit, PersistenceEventQueueInterface::UPDATE);

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
