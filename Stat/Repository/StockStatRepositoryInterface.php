<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stat\Repository;

use DateTime;
use Ekyna\Component\Commerce\Stat\Entity\StockStat;

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
     * @param DateTime|null $date
     *
     * @return StockStat|null
     */
    public function findOneByDay(DateTime $date = null): ?StockStat;

    /**
     * Returns the latest stock stats.
     *
     * @param int $limit
     *
     * @return StockStat[]
     */
    public function findLatest(int $limit = 30): array;
}
