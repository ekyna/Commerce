<?php

namespace Ekyna\Component\Commerce\Stock\Dispatcher;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

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
     * @param float              $quantity
     * @param int                $direction The source assignments sort direction (default SORT_DESC)
     *
     * @return float The quantity indeed moved
     *
     * @throws StockLogicException
     */
    public function moveAssignments(
        StockUnitInterface $sourceUnit,
        StockUnitInterface $targetUnit,
        $quantity,
        $direction = SORT_DESC
    );
}