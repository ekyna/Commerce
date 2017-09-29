<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockAssignmentUpdater
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentUpdater implements StockAssignmentUpdaterInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitUpdaterInterface
     */
    protected $stockUnitUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockUnitUpdaterInterface  $stockUnitUpdater
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper, StockUnitUpdaterInterface $stockUnitUpdater)
    {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUnitUpdater = $stockUnitUpdater;
    }

    /**
     * @inheritdoc
     */
    public function updateSold(StockAssignmentInterface $assignment, $quantity, $relative = true)
    {
        $stockUnit = $assignment->getStockUnit();

        $delta = $quantity;
        if (!$relative) {
            $delta -= $assignment->getSoldQuantity();
        }

        // TODO use Packaging format

        // Credit case
        if (0 < $delta) {
            if ($delta > $limit = $stockUnit->getReservableQuantity()) {
                $delta = $limit;
            }
        }
        // Debit case
        elseif (0 > $delta) {
            // Sold quantity can't be lower than shipped quantity
            if (0 < $assignment->getShippedQuantity() && $assignment->getShippedQuantity() <= abs($delta)) {
                $delta = -$assignment->getShippedQuantity();
            }
            elseif (0 < $stockUnit->getShippedQuantity() && $stockUnit->getShippedQuantity() <= abs($delta)) {
                $delta = -$stockUnit->getShippedQuantity();
            }
            // Sold quantity can't be lower than zero
            if ($assignment->getSoldQuantity() < abs($delta)) {
                $delta = -$assignment->getSoldQuantity();
            }
            elseif ($stockUnit->getSoldQuantity() < abs($delta)) {
                $delta = -$stockUnit->getSoldQuantity();
            }
        }
        if (0 == $delta) {
            return 0;
        }

        $quantity = $assignment->getSoldQuantity() + $delta;
        if (0 > $quantity) {
            throw new StockLogicException("Failed to update stock assignment's sold quantity.");
        }

        // Stock unit update
        $this->stockUnitUpdater->updateSold($assignment->getStockUnit(), $delta, true);

        // Assignment update
        if (0 == $quantity) {
            // Clear association
            $assignment->getSaleItem()->removeStockAssignment($assignment);
            // TODO Check if removal is safe
            $this->persistenceHelper->remove($assignment);
        } else {
            $assignment->setSoldQuantity($quantity);
            $this->persistenceHelper->persistAndRecompute($assignment);
        }

        return $relative ? $delta : $quantity;
    }

    /**
     * @inheritdoc
     */
    public function updateShipped(StockAssignmentInterface $assignment, $quantity, $relative = true)
    {
        $stockUnit = $assignment->getStockUnit();

        $delta = $quantity;
        if (!$relative) {
            $delta -= $assignment->getShippedQuantity();
        }

        // TODO use Packaging format

        if (0 < $delta) {
            // Credit case
            if ($delta > $limit = $assignment->getShippableQuantity()) {
                $delta = $limit;
            }
        } elseif (0 > $delta) {
            // Debit case
            if ($stockUnit->getShippedQuantity() < abs($delta)) {
                $delta = -$stockUnit->getShippedQuantity();
            }
            if ($assignment->getShippedQuantity() < abs($delta)) {
                $delta = -$assignment->getShippedQuantity();
            }
        }
        if (0 == $delta) {
            return 0;
        }

        $quantity = $assignment->getShippedQuantity() + $delta;
        if (0 > $quantity) {
            throw new StockLogicException("Failed to update stock assignment's shipped quantity.");
        }

        // Stock unit update
        $this->stockUnitUpdater->updateShipped($assignment->getStockUnit(), $delta, true);

        // Assignment update
        $assignment->setShippedQuantity($quantity);
        $this->persistenceHelper->persistAndRecompute($assignment);

        return $relative ? $delta : $quantity;
    }
}
