<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Updater;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface as Assignment;

/**
 * Interface StockAssignmentUpdaterInterface
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Rename to AssignmentUpdaterInterface
 */
interface StockAssignmentUpdaterInterface
{
    /**
     * Updates the assignment's sold quantity.
     *
     * @return Decimal The resulting updated quantity (relative).
     */
    public function updateSold(Assignment $assignment, Decimal $quantity, bool $relative): Decimal;

    /**
     * Updates the assignment's shipped quantity.
     *
     * @return Decimal The resulting updated quantity (relative).
     */
    public function updateShipped(Assignment $assignment, Decimal $quantity, bool $relative): Decimal;

    /**
     * Updates the assignment's locked quantity.
     *
     * @return Decimal The resulting updated quantity (relative).
     */
    public function updateLocked(Assignment $assignment, Decimal $quantity, bool $relative): Decimal;
}
