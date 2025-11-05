<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Updater;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface as Assignment;

/**
 * Class StockAssignmentUpdater
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentUpdater implements StockAssignmentUpdaterInterface
{
    public function __construct(
        protected readonly StockUnitUpdaterInterface       $stockUnitUpdater,
        protected readonly StockAssignmentManagerInterface $assignmentManager
    ) {
    }

    public function updateSold(Assignment $assignment, Decimal $quantity, bool $relative): Decimal
    {
        // TODO use Packaging format

        $unit = $assignment->getStockUnit();

        if (!$relative) {
            // Turn into relative quantity
            $quantity -= $assignment->getSoldQuantity();
        }

        // Positive update
        if (0 < $quantity) {
            // Sold quantity can't be greater than stock unit ordered + adjusted
            $quantity = min($quantity, $unit->getReservableQuantity());
        } // Negative update
        elseif (0 > $quantity) {
            // Sold quantity can't be lower than shipped quantity or zero
            $quantity = max(
                $quantity,
                $assignment->getShippedQuantity() - $assignment->getSoldQuantity(),
                $unit->getShippedQuantity() - $unit->getSoldQuantity()
            );
        } // No update
        else {
            return new Decimal(0);
        }

        // Stock unit update
        $this->stockUnitUpdater->updateSold($unit, $quantity, true);

        // Assignment update
        $assignment->setSoldQuantity($assignment->getSoldQuantity() + $quantity);
        $this->assignmentManager->persist($assignment);

        return $quantity;
    }

    public function updateShipped(Assignment $assignment, Decimal $quantity, bool $relative): Decimal
    {
        // TODO use Packaging format

        $unit = $assignment->getStockUnit();

        if (!$relative) {
            // Turn into relative quantity
            $quantity -= $assignment->getShippedQuantity();
        }

        // Positive update
        if (0 < $quantity) {
            // Shipped quantity can't be greater than received or sold quantity
            $quantity = min($quantity, $assignment->getShippableQuantity());
        } // Negative update
        elseif (0 > $quantity) {
            // Shipped quantity can't be lower than zero
            $quantity = max($quantity, -$assignment->getShippedQuantity(), -$unit->getShippedQuantity());
        } // No update
        else {
            return new Decimal(0);
        }

        // Stock unit update
        $this->stockUnitUpdater->updateShipped($unit, $quantity, true);

        // Assignment update
        $assignment->setShippedQuantity($assignment->getShippedQuantity() + $quantity);
        $this->assignmentManager->persist($assignment);

        return $quantity;
    }

    public function updateLocked(Assignment $assignment, Decimal $quantity, bool $relative): Decimal
    {
        // TODO use Packaging format

        $unit = $assignment->getStockUnit();

        if (!$relative) {
            // Turn into relative quantity
            $quantity -= $assignment->getLockedQuantity();
        }

        // Positive update
        if (0 < $quantity) {
            // Shipped quantity can't be greater than received or sold quantity
            $quantity = min($quantity, $assignment->getShippableQuantity());
        } // Negative update
        elseif (0 > $quantity) {
            // Shipped quantity can't be lower than zero
            $quantity = max($quantity, -$assignment->getLockedQuantity(), -$unit->getLockedQuantity());
        } // No update
        else {
            return new Decimal(0);
        }

        // Stock unit update
        $this->stockUnitUpdater->updateLocked($unit, $quantity, true);

        // Assignment update
        $assignment->setLockedQuantity($assignment->getLockedQuantity() + $quantity);
        $this->assignmentManager->persist($assignment);

        return $quantity;
    }
}
