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
        // TODO use Packaging format

        $stockUnit = $assignment->getStockUnit();

        if (!$relative) {
            $quantity -= $assignment->getSoldQuantity();
        }

        // Positive update
        if (0 < $quantity) {
            // Sold quantity can't be greater than stock unit ordered
            if ($quantity > $limit = $stockUnit->getReservableQuantity()) {
                $quantity = $limit;
            }
        }
        // Negative update
        elseif (0 > $quantity) {
            // Sold quantity can't be lower than shipped quantity or zero
            $limit = max(
                $assignment->getShippedQuantity() - $assignment->getSoldQuantity(),
                $stockUnit->getShippedQuantity() - $stockUnit->getSoldQuantity()
            );
            if ($quantity < $limit) {
                $quantity = $limit;
            }
        }
        // No update
        if (0 == $quantity) {
            return 0;
        }

        // Stock unit update
        $this->stockUnitUpdater->updateSold($stockUnit, $quantity, true);

        // Assignment update
        $result = $assignment->getSoldQuantity() + $quantity;
        if (0 == $result) {
            // Clear association
            $assignment->getSaleItem()->removeStockAssignment($assignment);
            // TODO Check if removal is safe
            $this->persistenceHelper->remove($assignment, false);
        } else {
            $assignment->setSoldQuantity($result);
            $this->persistenceHelper->persistAndRecompute($assignment, false);
        }

        return $quantity;
    }

    /**
     * @inheritdoc
     */
    public function updateShipped(StockAssignmentInterface $assignment, $quantity, $relative = true)
    {
        // TODO use Packaging format

        $stockUnit = $assignment->getStockUnit();

        if (!$relative) {
            $quantity -= $assignment->getShippedQuantity();
        }

        // Positive update
        if (0 < $quantity) {
            // Shipped quantity can't be greater than received or sold quantity
            if ($quantity > $limit = $assignment->getShippableQuantity()) {
                $quantity = $limit;
            }
        }
        // Negative update
        elseif (0 > $quantity) {
            // Shipped quantity can't be lower than zero
            if ($quantity < $limit = max(-$assignment->getShippedQuantity(), -$stockUnit->getShippedQuantity())) {
                $quantity = $limit;
            }
        }
        // No update
        if (0 == $quantity) {
            return 0;
        }

        // Stock unit update
        $this->stockUnitUpdater->updateShipped($stockUnit, $quantity, true);

        // Assignment update
        $assignment->setShippedQuantity($assignment->getShippedQuantity() + $quantity);
        $this->persistenceHelper->persistAndRecompute($assignment, false);

        return $quantity;
    }
}
