<?php

namespace Ekyna\Component\Commerce\Stock\Logger;

use Ekyna\Component\Commerce\Stock\Model;

/**
 * Interface StockLoggerInterface
 * @package Ekyna\Component\Commerce\Stock\Logger
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockLoggerInterface
{
    /**
     * Logs the stock unit sold quantity change.
     *
     * @param Model\StockUnitInterface $unit
     * @param float              $quantity
     * @param bool               $relative
     */
    public function unitSold(Model\StockUnitInterface $unit, $quantity, $relative = true);

    /**
     * Logs the stock assignment sold quantity change.
     *
     * @param Model\StockAssignmentInterface $assignment
     * @param float                    $quantity
     * @param bool                     $relative
     */
    public function assignmentSold(Model\StockAssignmentInterface $assignment, $quantity, $relative = true);

    /**
     * Logs the stock assignment unit change.
     *
     * @param Model\StockAssignmentInterface $assignment
     * @param Model\StockUnitInterface       $unit
     */
    public function assignmentUnit(Model\StockAssignmentInterface $assignment, Model\StockUnitInterface $unit);
}