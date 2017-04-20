<?php

declare(strict_types=1);

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
     * Resets the subject's stock data and state.
     */
    public function reset(StockSubjectInterface  $subject): void;

    /**
     * Updates the subject's stock data and state.
     *
     * @return bool Whether the subject has been updated.
     */
    public function update(StockSubjectInterface $subject): bool;
}
