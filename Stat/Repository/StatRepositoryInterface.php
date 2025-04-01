<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Repository;

use DateTime;
use Ekyna\Component\Commerce\Stat\Model\StatInterface;
use Ekyna\Component\Resource\Model\DateRange;
use Exception;

/**
 * Interface StatRepositoryInterface
 * @package Ekyna\Component\Commerce\Stat\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @template S
 */
interface StatRepositoryInterface
{
    /**
     * Finds the order stat by day.
     *
     * @return S|null
     */
    public function findOneByDay(DateTime $date): ?StatInterface;

    /**
     * Finds the order stat by month.
     *
     * @return S|null
     */
    public function findOneByMonth(DateTime $date): ?StatInterface;

    /**
     * Finds the order stat by year.
     *
     * @return S|null
     */
    public function findOneByYear(string $year): ?StatInterface;

    /**
     * Returns the daily stats sums of the given date range.
     *
     * @throws Exception
     */
    public function findSumByDateRange(DateRange $range): array;

    public function findRevenues(int $type, DateRange $range): array;
}
