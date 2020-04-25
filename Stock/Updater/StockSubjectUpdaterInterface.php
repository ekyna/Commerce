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
     * Updates the subject's stock data and state.
     *
     * @param StockSubjectInterface $subject
     *
     * @return bool Whether or not the subject has been updated.
     */
    public function update(StockSubjectInterface $subject): bool;
}
