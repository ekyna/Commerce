<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitUpdater
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitUpdater implements StockUnitUpdaterInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var StockUnitCacheInterface
     */
    private $stockUnitCache;

    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockUnitCacheInterface    $stockUnitCache
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitCacheInterface $stockUnitCache
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUnitCache = $stockUnitCache;
    }

    /**
     * @inheritdoc
     */
    public function updateOrdered(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getOrderedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new InvalidArgumentException("Unexpected ordered quantity.");
        }

        // Prevent ordered quantity to be set as lower than the received quantity
        if ($quantity < $stockUnit->getReceivedQuantity()) {
            throw new InvalidArgumentException("The ordered quantity can't be lower than the received quantity.");
        }

        $stockUnit->setOrderedQuantity($quantity);

        $this->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateReceived(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getReceivedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new InvalidArgumentException("Unexpected received quantity.");
        }

        // Prevent received quantity to be set as greater than the ordered quantity
        if ($quantity > $stockUnit->getOrderedQuantity()) {
            throw new InvalidArgumentException("The received quantity can't be greater than the ordered quantity.");
        }

        $stockUnit->setReceivedQuantity($quantity);

        $this->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateSold(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getSoldQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new InvalidArgumentException("Unexpected sold quantity.");
        }

        // Prevent sold quantity to be set as lower than the shipped quantity
        if ($quantity < $stockUnit->getShippedQuantity()) {
            throw new InvalidArgumentException("The sold quantity can't be lower than the shipped quantity.");
        }

        $stockUnit->setSoldQuantity($quantity);

        $this->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateShipped(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getShippedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new InvalidArgumentException("Unexpected shipped quantity.");
        }

        // Prevent shipped quantity to be set as greater than the sold or received quantity
        if ($quantity > $stockUnit->getSoldQuantity()) {
            throw new InvalidArgumentException("The shipped quantity can't be greater than the sold quantity.");
        }
        if ($quantity > $stockUnit->getReceivedQuantity()) {
            throw new InvalidArgumentException("The shipped quantity can't be greater than the received quantity.");
        }

        $stockUnit->setShippedQuantity($quantity);

        $this->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateNetPrice(StockUnitInterface $stockUnit, $netPrice)
    {
        if (0 > $netPrice) {
            throw new InvalidArgumentException("Unexpected net price.");
        }

        if ($netPrice != $stockUnit->getNetPrice()) {
            $stockUnit->setNetPrice($netPrice);

            $this->persistenceHelper->persistAndRecompute($stockUnit, true);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateEstimatedDateOfArrival(StockUnitInterface $stockUnit, \DateTime $date)
    {
        if ($date != $stockUnit->getEstimatedDateOfArrival()) {
            $stockUnit->setEstimatedDateOfArrival($date);

            $this->persistenceHelper->persistAndRecompute($stockUnit, true);

            return true;
        }

        return false;
    }

    /**
     * Persists the stock unit, or removes it if empty.
     *
     * @param StockUnitInterface $stockUnit
     */
    protected function persistOrRemove(StockUnitInterface $stockUnit)
    {
        // TODO refactor stock unit validation here ?

        if ($stockUnit->isEmpty()) {
            // TODO Check if removal is safe
            // TODO Clear association
            $this->persistenceHelper->remove($stockUnit, true);
            $this->stockUnitCache->remove($stockUnit);
        } else {
            $this->persistenceHelper->persistAndRecompute($stockUnit, true);
            // Caches the stock unit to make it available for the StockSubjectUpdater.
            $this->stockUnitCache->add($stockUnit);
        }
    }
}
