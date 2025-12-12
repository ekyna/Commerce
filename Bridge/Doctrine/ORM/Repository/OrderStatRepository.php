<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Stat\Model\StatInterface;
use Ekyna\Component\Commerce\Stat\Repository\OrderStatRepositoryInterface;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Class OrderStatRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStatRepository extends AbstractStatRepository implements OrderStatRepositoryInterface
{
    public function findSumByDateRange(DateRange $range): array
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        return $qb
            ->select([
                'SUM(o.revenue) as revenue',
                'SUM(o.shipping) as shipping',
                'SUM(o.cost) as cost',
                'SUM(o.count) as count',
                'AVG(o.average) as average',
            ])
            ->andWhere($ex->eq('o.type', ':type'))
            ->andWhere($ex->between('o.date', ':from', ':to'))
            ->setParameters([
                'type' => StatInterface::TYPE_DAY,
                'from' => $range->getStart(),
                'to'   => $range->getEnd(),
            ])
            ->getQuery()
            ->getScalarResult();
    }
}
