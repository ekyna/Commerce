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
     */
    public function updateOrdered(StockUnitInterface $stockUnit, $quantity);

    /**
     * Updates the estimated date of arrival.
     *
     * @param StockUnitInterface $stockUnit
     * @param \DateTime $date
     */
    public function updateEstimatedDateOfArrival(StockUnitInterface $stockUnit, \DateTime $date);

    /**
     * Updates the delivered quantity (from supplier).
     *
     * @param StockUnitInterface $stockUnit
     * @param float              $quantity
     */
    public function updateDelivered(StockUnitInterface $stockUnit, $quantity);

    /**
     * Updates the shipped quantity (to customers).
     *
     * @param StockUnitInterface $stockUnit
     * @param float              $quantity
     */
    public function updateShipped(StockUnitInterface $stockUnit, $quantity);
}
