<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Dispatcher;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface as Assignment;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface as Unit;

/**
 * Interface StockAssignmentDispatcherInterface
 * @package Ekyna\Component\Commerce\Stock\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAssignmentDispatcherInterface
{
    /**
     * Moves (or splits) assignments from the source stock unit
     * to the target stock unit for the given quantity.
     *
     * @param StockUnitInterface $sourceUnit
     * @param StockUnitInterface $targetUnit
     * @param Decimal            $quantity
     * @param int                $direction The source assignments sort direction (default SORT_DESC)
     *
     * @return Decimal The quantity indeed moved
     *
     * @throws StockLogicException
     */
    public function moveAssignments(
        StockUnitInterface $sourceUnit,
        StockUnitInterface $targetUnit,
        Decimal            $quantity,
        int                $direction = SORT_DESC
    ): Decimal;

    /**
     * Move the given assignment to the given unit for the given sold quantity.
     *
     * @param Assignment $assignment
     * @param Unit       $targetUnit
     * @param Decimal    $quantity
     *
     * @return Decimal The quantity moved
     */
    public function moveAssignment(Assignment $assignment, Unit $targetUnit, Decimal $quantity): Decimal;
}
