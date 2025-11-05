<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Logger;

use Decimal\Decimal;
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
     */
    public function unitSold(Model\StockUnitInterface $unit, Decimal $quantity, bool $relative = true): void;

    /**
     * Logs the stock assignment sold quantity change.
     */
    public function assignmentSold(Model\AssignmentInterface $assignment, Decimal $quantity, bool $relative = true): void;

    /**
     * Logs the stock assignment unit change.
     */
    public function assignmentUnit(Model\AssignmentInterface $assignment, Model\StockUnitInterface $unit): void;
}
