<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;

/**
 * Interface StockAssignmentUpdaterInterface
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAssignmentUpdaterInterface
{
    /**
     * Updates the assignment's sold quantity.
     *
     * @param StockAssignmentInterface $assignment
     * @param float                    $quantity
     * @param bool                     $relative
     *
     * @return float The resulting updated quantity (relative or absolute).
     */
    public function updateSold(StockAssignmentInterface $assignment, $quantity, $relative = true);

    /**
     * Updates the assignment's shipped quantity.
     *
     * @param StockAssignmentInterface $assignment
     * @param float                    $quantity
     * @param bool                     $relative
     *
     * @return float The resulting updated quantity (relative or absolute).
     */
    public function updateShipped(StockAssignmentInterface $assignment, $quantity, $relative = true);
}
