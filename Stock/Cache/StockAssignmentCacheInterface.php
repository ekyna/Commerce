<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Cache;

use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockAssignmentCacheInterface
 * @package Ekyna\Component\Commerce\Stock\Cache
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    Rename to AssignmentCacheInterface
 */
interface StockAssignmentCacheInterface
{
    /**
     * Removes the stock assignment.
     *
     * @param AssignmentInterface $assignment
     */
    public function addRemoved(AssignmentInterface $assignment): void;

    /**
     * Finds a stock assignment by stock unit and sale item.
     *
     * @param StockUnitInterface  $unit
     * @param AssignableInterface $assignable
     *
     * @return AssignmentInterface|null
     */
    public function findRemoved(StockUnitInterface $unit, AssignableInterface $assignable): ?AssignmentInterface;

    /**
     * Returns all the cached removed assignments.
     *
     * @return AssignmentInterface[]
     */
    public function flush(): array;
}
