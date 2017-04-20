<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Ekyna\Component\Commerce\Stock\Repository\StockAdjustmentRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class StockAdjustmentRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentRepository extends ResourceRepository implements StockAdjustmentRepositoryInterface
{
    public function findByMonth(DateTime $month): array
    {
        $start = clone $month;
        $start
            ->modify('first day of this month')
            ->setTime(0, 0);

        $end = clone $month;
        $end
            ->modify('last day of this month')
            ->setTime(23, 59, 59, 999999);

        $qb = $this->createQueryBuilder();

        return $qb
            ->andWhere($qb->expr()->between('a.createdAt', ':start', ':end'))
            ->getQuery()
            ->setParameter('start', $start, Types::DATETIME_MUTABLE)
            ->setParameter('end', $end, Types::DATETIME_MUTABLE)
            ->getResult();
    }

    protected function getAlias(): string
    {
        return 'a';
    }
}
