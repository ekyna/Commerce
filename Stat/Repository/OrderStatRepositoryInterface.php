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
     * Finds the day revenues by month.
     *
     * @param \DateTime $date
     *
     * @return float[]
     */
    public function findDayRevenuesByMonth(\DateTime $date);

    /**
     * Finds the order stat by year.
     *
     * @param \DateTime $date
     *
     * @return \Ekyna\Component\Commerce\Stat\Entity\OrderStat|null
     */
    public function findOneByYear(\DateTime $date);

    /**
     * Finds the month revenues by year.
     *
     * @param \DateTime $date
     *
     * @return float[]
     */
    public function findMonthRevenuesByYear(\DateTime $date);

    /**
     * Finds the year revenues.
     *
     * @param int $limit
     *
     * @return float[]
     */
    public function findYearRevenues($limit = 8);
}