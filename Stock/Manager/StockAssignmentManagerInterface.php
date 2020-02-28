<?php

namespace Ekyna\Component\Commerce\Stock\Manager;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockAssignmentManagerInterface
 * @package Ekyna\Component\Commerce\Stock\Manager
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAssignmentManagerInterface
{
    /**
     * Persists the stock assignment.
     *
     * @param StockAssignmentInterface $assignment
     */
    public function persist(StockAssignmentInterface $assignment): void;

    /**
     * Removes the stock assignment.
     *
     * @param StockAssignmentInterface $assignment
     * @param bool $hard Whether to remove assignment even if it has an id
     */
    public function remove(StockAssignmentInterface $assignment, bool $hard = false): void;

    /**
     * Creates the stock assignment for the given sale item and stock unit.
     *
     * @param SaleItemInterface       $item
     * @param StockUnitInterface|null $unit
     *
     * @return StockAssignmentInterface
     */
    public function create(SaleItemInterface $item, StockUnitInterface $unit = null): StockAssignmentInterface;
}
