<?php

namespace Ekyna\Component\Commerce\Stock\Util;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class StockUtil
 * @package Ekyna\Component\Commerce\Stock\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockUtil
{
    /**
     * Calculates the "in" stock quantity.
     *
     * (received - sold, 0 or greater)
     *
     * @param float $receivedQty
     * @param float $soldQty
     *
     * @return float
     */
    static public function calculateInStock($receivedQty, $soldQty)
    {
        $result = $receivedQty - $soldQty;

        return 0 < $result ? $result : 0;
    }

    /**
     * Calculates the "virtual" stock quantity.
     *
     * (ordered - max(received or sold), 0 or greater)
     *
     * @param float $orderedQty
     * @param float $receivedQty
     * @param float $soldQty
     *
     * @return float
     */
    static public function calculateVirtualStock($orderedQty, $receivedQty, $soldQty)
    {
        $result = $orderedQty - max($receivedQty, $soldQty);

        return 0 < $result ? $result : 0;
    }

    /**
     * Calculates the "reservable" stock quantity.
     *
     * (ordered - sold, 0 or greater)
     *
     * @param float $orderedQty
     * @param float $soldQty
     *
     * @return float
     */
    static public function calculateReservable($orderedQty, $soldQty)
    {
        if (0 == $orderedQty) {
            return INF;
        }

        $result = $orderedQty - $soldQty;

        return 0 < $result ? $result : 0;
    }

    /**
     * Calculates the "shippable" stock quantity.
     *
     *  (min(received or sold) - shipped, 0 or greater)
     *
     * @param float $receivedQty
     * @param float $soldQty
     * @param float $shippedQty
     *
     * @return float
     */
    static public function calculateShippable($receivedQty, $soldQty, $shippedQty)
    {
        $result = min($receivedQty, $soldQty) - $shippedQty;

        return 0 < $result ? $result : 0;
    }

    /**
     * Returns whether or not the given stock unit can be safely deleted.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return bool
     */
    static public function isDeletableStockUnit(StockUnitInterface $stockUnit)
    {
        if (
            0 < $stockUnit->getReceivedQuantity() ||
            0 < $stockUnit->getSoldQuantity() ||
            0 < $stockUnit->getShippedQuantity()
        ) {
            return false;
        }

        return true;
    }
}
