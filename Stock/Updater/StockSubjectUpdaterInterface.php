<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;

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
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateInStock(StockSubjectInterface $subject);

    /**
     * 2. Updates the subject's "ordered stock" quantity.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function updateOrderedStock(StockSubjectInterface $subject);

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
     * Updates the subject's stock and state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function update(StockSubjectInterface $subject);
}
