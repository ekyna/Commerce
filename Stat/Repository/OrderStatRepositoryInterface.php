<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Repository;

use DateTime;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Exception;

/**
 * Interface OrderStatRepositoryInterface
 * @package Ekyna\Component\Commerce\Stat\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderStatRepositoryInterface
{
    /**
     * Finds the order stat by day.
     */
    public function findOneByDay(DateTime $date): ?OrderStat;

    /**
     * Finds the order stat by month.
     */
    public function findOneByMonth(DateTime $date): ?OrderStat;

    /**
     * Finds the order stat by year.
     */
    public function findOneByYear(DateTime $date): ?OrderStat;

    /**
     * Returns the daily stats sums of the given year (between the january, 1st and the given date).
     *
     * @throws Exception
     */
    public function findSumByYear(DateTime $date): OrderStat;

    /**
     * Finds the day revenues by month.
     */
    public function findDayRevenuesByMonth(DateTime $date, bool $detailed = false): array;

    /**
     * Finds the month revenues by year.
     */
    public function findMonthRevenuesByYear(DateTime $date, bool $detailed = false): array;

    /**
     * Finds the year revenues.
     */
    public function findYearRevenues(int $limit = 8, bool $detailed = false): array;
}
