<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Updater;

use DateTime;

/**
 * Interface StatUpdaterInterface
 * @package Ekyna\Component\Commerce\Stat\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StatUpdaterInterface
{
    /**
     * Updates the order stats for the given day.
     *
     * @return bool Whether the stat has been created.
     */
    public function updateDayOrderStat(DateTime $date, bool $force = false): bool;

    /**
     * Updates the order stats for the given month.
     *
     * @return bool Whether the stat has been created.
     */
    public function updateMonthOrderStat(DateTime $date, bool $force = false): bool;

    /**
     * Updates the order stats for the given year.
     *
     * @return bool Whether the stat has been created.
     */
    public function updateYearOrderStat(string $year, bool $force = false): bool;
}
