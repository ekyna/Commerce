<?php

namespace Ekyna\Component\Commerce\Stock\Cache;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class StockAssignmentCache
 * @package Ekyna\Component\Commerce\Stock\Cache
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentCache implements StockAssignmentCacheInterface
{
    /**
     * @var StockAssignmentInterface[]
     */
    private $removedAssignments = [];


    /**
     * @inheritDoc
     */
    public function addRemoved(StockAssignmentInterface $assignment): void
    {
        $key = $this->buildKey($assignment->getStockUnit(), $assignment->getSaleItem());

        $assignment
            ->setSaleItem(null)
            ->setStockUnit(null);

        $this->removedAssignments[$key] = $assignment;
    }

    /**
     * @inheritDoc
     */
    public function findRemoved(StockUnitInterface $unit, SaleItemInterface $item): ?StockAssignmentInterface
    {
        $key = $this->buildKey($unit, $item);

        if (isset($this->removedAssignments[$key])) {
            $assignment = $this->removedAssignments[$key];
            unset($this->removedAssignments[$key]);

            return $assignment;
        }

        return null;
    }

    /**
     * Returns all the cached removed assignments.
     *
     * @return StockAssignmentInterface[]
     */
    public function flush(): array
    {
        $assignments = $this->removedAssignments;

        $this->removedAssignments = [];

        return $assignments;
    }

    /**
     * Builds the assignment key.
     *
     * @param StockUnitInterface $unit
     * @param SaleItemInterface  $item
     *
     * @return string
     */
    private function buildKey(StockUnitInterface $unit, SaleItemInterface $item): ?string
    {
        return sprintf("%s-%s", spl_object_hash($unit), spl_object_hash($item));
    }
}
