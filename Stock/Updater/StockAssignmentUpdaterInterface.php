<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface as Assignment;

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
     * @param Assignment $assignment
     * @param float      $quantity
     * @param bool       $relative
     *
     * @return float The resulting updated quantity (relative).
     */
    public function updateSold(Assignment $assignment, float $quantity, bool $relative = true): float;

    /**
     * Updates the assignment's shipped quantity.
     *
     * @param Assignment $assignment
     * @param float      $quantity
     * @param bool       $relative
     *
     * @return float The resulting updated quantity (relative).
     */
    public function updateShipped(Assignment $assignment, float $quantity, bool $relative = true): float;

    /**
     * Updates the assignment's locked quantity.
     *
     * @param Assignment $assignment
     * @param float      $quantity
     * @param bool       $relative
     *
     * @return float The resulting updated quantity (relative).
     */
    public function updateLocked(Assignment $assignment, float $quantity, bool $relative = true): float;
}
