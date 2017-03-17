<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface StockSubjectUpdaterInterface
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockSubjectUpdaterInterface
{
    /**
     * 1. Updates the subject's "in stock" quantity.
     *
     * @param StockSubjectInterface $subject
     * @param float                 $quantity
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateInStock(StockSubjectInterface $subject, $quantity = null);

    /**
     * 2. Updates the subject's "virtual stock" quantity.
     *
     * @param StockSubjectInterface $subject
     * @param float                 $quantity
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateVirtualStock(StockSubjectInterface $subject, $quantity = null);

    /**
     * 3. Updates the subject's estimated date of arrival date.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateEstimatedDateOfArrival(StockSubjectInterface $subject);

    /**
     * 4. Updates the subject's stock state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateStockState(StockSubjectInterface $subject);

    /**
     * Updates the subject's stocks and state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function update(StockSubjectInterface $subject);

    /**
     * Updates the subject's stocks and state regarding to the stock unit changes (persistence event).
     *
     * @param StockSubjectInterface $subject
     * @param StockUnitInterface    $stockUnit
     *
     * @return bool
     */
    public function updateFromStockUnitChange(StockSubjectInterface $subject, StockUnitInterface $stockUnit);

    /**
     * Updates the subject's stocks and state regarding to the stock unit removal (persistence event).
     *
     * @param StockSubjectInterface $subject
     * @param StockUnitInterface    $stockUnit
     *
     * @return bool
     */
    public function updateFromStockUnitRemoval(StockSubjectInterface $subject, StockUnitInterface $stockUnit);
}
