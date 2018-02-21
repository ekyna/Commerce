<?php

namespace Ekyna\Component\Commerce\Stat\Repository;

/**
 * Interface StockStatRepositoryInterface
 * @package Ekyna\Component\Commerce\Stat\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockStatRepositoryInterface
{
    /**
     * Finds the stock stat by day.
     *
     * @param \DateTime $date
     *
     * @return \Ekyna\Component\Commerce\Stat\Entity\StockStat|null
     */
    public function findOneByDay(\DateTime $date = null);

    /**
     * Returns the latest stock stats.
     *
     * @param int $limit
     *
     * @return \Ekyna\Component\Commerce\Stat\Entity\StockStat[]
     */
    public function findLatest($limit = 30);
}