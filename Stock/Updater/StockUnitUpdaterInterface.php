<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockUnitUpdaterInterface
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitUpdaterInterface
{
    /**
     * Updates the ordered quantity (to supplier).
     *
     * @param StockUnitInterface $stockUnit
     * @param float              $quantity
     * @param bool               $relative
     */
    public function updateOrdered(StockUnitInterface $stockUnit, $quantity, $relative = true);

    /**
     * Updates the delivered quantity (from supplier).
     *
     * @param StockUnitInterface $stockUnit
     * @param float              $quantity
     * @param bool               $relative
     */
    public function updateDelivered(StockUnitInterface $stockUnit, $quantity, $relative = true);

    /**
     * Updates the shipped quantity (to customers).
     *
     * @param StockUnitInterface $stockUnit
     * @param float              $quantity
     * @param bool               $relative
     */
    public function updateShipped(StockUnitInterface $stockUnit, $quantity, $relative = true);

    /**
     * Updates the estimated date of arrival.
     *
     * @param StockUnitInterface $stockUnit
     * @param \DateTime $date
     */
    public function updateEstimatedDateOfArrival(StockUnitInterface $stockUnit, \DateTime $date);
}
