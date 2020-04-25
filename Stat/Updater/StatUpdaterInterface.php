<?php

namespace Ekyna\Component\Commerce\Stat\Updater;

/**
 * Interface StatUpdaterInterface
 * @package Ekyna\Component\Commerce\Stat\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StatUpdaterInterface
{
    /**
     * Updates the stock stats for today.
     *
     * @return bool Whether or not the stat has been created.
     */
    public function updateStockStat(): bool;

    /**
     * Updates the order stats for the given day.
     *
     * @param \DateTime $date
     * @param bool      $force
     *
     * @return bool
     */
    public function updateDayOrderStat(\DateTime $date, bool $force = false): bool;

    /**
     * Updates the order stats for the given month.
     *
     * @param \DateTime $date
     * @param bool      $force
     *
     * @return bool
     */
    public function updateMonthOrderStat(\DateTime $date, bool $force = false): bool;

    /**
     * Updates the order stats for the given year.
     *
     * @param \DateTime $date
     * @param bool      $force
     *
     * @return bool
     */
    public function updateYearOrderStat(\DateTime $date, bool $force = false): bool;
}
