<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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
    public function updateReserved(StockAssignmentInterface $assignment, $quantity, $relative = true)
    {
        $stockUnit = $assignment->getStockUnit();

        $delta = $quantity;
        if (!$relative) {
            $delta -= $assignment->getReservedQuantity();
        }

        // TODO use Packaging format

        if (0 < $delta) {
            // Credit case
            if ($delta > $limit = $stockUnit->getReservableQuantity()) {
                $delta = $limit;
            }
        } elseif (0 > $delta) {
            // Debit case
            if ($stockUnit->getReservedQuantity() < abs($delta)) {
                $delta = -$stockUnit->getReservedQuantity();
            }
            if ($assignment->getReservedQuantity() < abs($delta)) {
                $delta = -$assignment->getReservedQuantity();
            }
        }
        if (0 == $delta) {
            return 0;
        }

        $quantity = $assignment->getReservedQuantity() + $delta;
        if (0 > $quantity) {
            throw new InvalidArgumentException("Unexpected reserved quantity.");
        }

        // Stock unit update
        $this->stockUnitUpdater->updateReserved($assignment->getStockUnit(), $delta, true);

        // Assignment update
        if (0 == $quantity) {
            // TODO Check if removal is safe
            // TODO Clear association
            $this->persistenceHelper->remove($assignment);
        } else {
            $assignment->setReservedQuantity($quantity);
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
            throw new InvalidArgumentException("Unexpected shipped quantity.");
        }

        // Stock unit update
        $this->stockUnitUpdater->updateShipped($assignment->getStockUnit(), $delta, true);

        // Assignment update
        $assignment->setShippedQuantity($quantity);
        $this->persistenceHelper->persistAndRecompute($assignment);

        return $relative ? $delta : $quantity;
    }
}
