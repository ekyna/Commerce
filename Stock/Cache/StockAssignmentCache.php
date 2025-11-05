<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Cache;

use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class StockAssignmentCache
 * @package Ekyna\Component\Commerce\Stock\Cache
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentCache implements StockAssignmentCacheInterface
{
    /**
     * @var array<string, AssignmentInterface>
     */
    private array $removedAssignments = [];


    /**
     * @inheritDoc
     */
    public function addRemoved(AssignmentInterface $assignment): void
    {
        $key = $this->buildKey($assignment->getStockUnit(), $assignment->getAssignable());

        $assignment
            ->setAssignable(null)
            ->setStockUnit(null);

        $this->removedAssignments[$key] = $assignment;
    }

    /**
     * @inheritDoc
     */
    public function findRemoved(StockUnitInterface $unit, AssignableInterface $assignable): ?AssignmentInterface
    {
        $key = $this->buildKey($unit, $assignable);

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
     * @return array<string, AssignmentInterface>
     */
    public function flush(): array
    {
        $assignments = $this->removedAssignments;

        $this->removedAssignments = [];

        return $assignments;
    }

    /**
     * Builds the assignment key.
     */
    private function buildKey(StockUnitInterface $unit, AssignableInterface $assignable): string
    {
        return sprintf('%d-%d', spl_object_id($unit), spl_object_id($assignable));
    }
}
