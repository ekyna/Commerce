<?php

namespace Ekyna\Component\Commerce\Stock\Cache;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockAssignmentCacheInterface
 * @package Ekyna\Component\Commerce\Stock\Cache
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAssignmentCacheInterface
{
    /**
     * Removes the stock assignment.
     *
     * @param StockAssignmentInterface $assignment
     */
    public function remove(StockAssignmentInterface $assignment): void;

    /**
     * Finds a stock assignment by stock unit and sale item.
     *
     * @param StockUnitInterface $unit
     * @param SaleItemInterface  $item
     *
     * @return StockAssignmentInterface|null
     */
    public function findRemoved(StockUnitInterface $unit, SaleItemInterface $item): ?StockAssignmentInterface;

    /**
     * Returns all the cached removed assignments.
     *
     * @return StockAssignmentInterface[]
     */
    public function flush(): array;
}
