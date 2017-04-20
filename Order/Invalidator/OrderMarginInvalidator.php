<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Invalidator;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class OrderMarginInvalidator
 * @package Ekyna\Component\Commerce\Stat\Invalidator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class OrderMarginInvalidator
{
    /** @var int[] */
    protected array $unitIds;


    public function __construct()
    {
        $this->clear();
    }

    /**
     * Clears the stock unit ids.
     */
    protected function clear(): void
    {
        $this->unitIds = [];
    }

    /**
     * Stores the identifier of the stock unit (whose cost price has changed).
     *
     * @param StockUnitInterface $unit
     */
    public function addStockUnit(StockUnitInterface $unit): void
    {
        if (0 >= $id = $unit->getId()) {
            return;
        }

        if (in_array($id, $this->unitIds, true)) {
            return;
        }

        $this->unitIds[] = $id;
    }

    /**
     * Invalidates orders margin total based on stored stock unit identifiers.
     */
    abstract public function invalidate(): void;
}
