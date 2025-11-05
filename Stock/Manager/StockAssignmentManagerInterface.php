<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Manager;

use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockAssignmentManagerInterface
 * @package Ekyna\Component\Commerce\Stock\Manager
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Rename to AssignmentManagerInterface
 */
interface StockAssignmentManagerInterface
{
    /**
     * Persists the stock assignment.
     *
     * @param AssignmentInterface $assignment
     */
    public function persist(AssignmentInterface $assignment): void;

    /**
     * Removes the stock assignment.
     *
     * @param AssignmentInterface $assignment
     * @param bool                $hard Whether to remove assignment even if it has an id
     */
    public function remove(AssignmentInterface $assignment, bool $hard = false): void;

    /**
     * Creates the stock assignment for the given sale item and stock unit.
     *
     * @param AssignableInterface     $assignable
     * @param StockUnitInterface|null $unit
     *
     * @return AssignmentInterface
     */
    public function create(AssignableInterface $assignable, StockUnitInterface $unit = null): AssignmentInterface;
}
