<?php

namespace Ekyna\Component\Commerce\Stat\Repository;

/**
 * Interface OrderStatRepositoryInterface
 * @package Ekyna\Component\Commerce\Stat\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderStatRepositoryInterface
{
    /**
     * Finds the order stat by day.
     *
     * @param \DateTime $date
     *
     * @return \Ekyna\Component\Commerce\Stat\Entity\OrderStat|null
     */
    public function findOneByDay(\DateTime $date);

    /**
     * Finds the order stat by month.
     *
     * @param \DateTime $date
     *
     * @return \Ekyna\Component\Commerce\Stat\Entity\OrderStat|null
     */
    public function findOneByMonth(\DateTime $date);

    /**
     * Finds the order stat by year.
     *
     * @param \DateTime $date
     *
     * @return \Ekyna\Component\Commerce\Stat\Entity\OrderStat|null
     */
    public function findOneByYear(\DateTime $date);

    /**
     * Returns the daily stats sums of the given year (between the january, 1st and the given date).
     *
     * @param \DateTime $date
     *
     * @return \Ekyna\Component\Commerce\Stat\Entity\OrderStat
     * @throws \Exception
     */
    public function findSumByYear(\DateTime $date);

    /**
     * Finds the day revenues by month.
     *
     * @param \DateTime $date
     * @param bool  $detailed
     *
     * @return float[]
     */
    public function findDayRevenuesByMonth(\DateTime $date, $detailed = false);

    /**
     * Finds the month revenues by year.
     *
     * @param \DateTime $date
     * @param bool  $detailed
     *
     * @return float[]
     */
    public function findMonthRevenuesByYear(\DateTime $date, $detailed = false);

    /**
     * Finds the year revenues.
     *
     * @param int $limit
     * @param bool  $detailed
     *
     * @return float[]
     */
    public function findYearRevenues($limit = 8, $detailed = false);
}