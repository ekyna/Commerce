<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Ekyna\Component\Commerce\Stat\Repository\OrderStatRepositoryInterface;

/**
 * Class OrderStatRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStatRepository extends EntityRepository implements OrderStatRepositoryInterface
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $revenueQuery;


    /**
     * @inheritDoc
     */
    public function findOneByDay(\DateTime $date)
    {
        return $this->findOneBy([
            'type' => OrderStat::TYPE_DAY,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findOneByMonth(\DateTime $date)
    {
        return $this->findOneBy([
            'type' => OrderStat::TYPE_MONTH,
            'date' => $date->format('Y-m'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findDayRevenuesByMonth(\DateTime $date)
    {
        return $this->findRevenues(OrderStat::TYPE_DAY, $date);
    }

    /**
     * @inheritDoc
     */
    public function findOneByYear(\DateTime $date)
    {
        return $this->findOneBy([
            'type' => OrderStat::TYPE_YEAR,
            'date' => $date->format('Y'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function findMonthRevenuesByYear(\DateTime $date)
    {
        return $this->findRevenues(OrderStat::TYPE_MONTH, $date);
    }

    /**
     * @inheritDoc
     */
    public function findYearRevenues($limit = 8)
    {
        $qb = $this->createQueryBuilder('o');
        $expr = $qb->expr();

        $result = $qb
            ->select(['o.date', 'o.revenue'])
            ->andWhere($expr->eq('o.type', ':type'))
            ->addOrderBy('o.date')
            ->getQuery()
            ->setParameter('type', OrderStat::TYPE_YEAR)
            ->setMaxResults($limit = 8)
            ->getScalarResult();

        $data = [];
        foreach ($result as $r) {
            $data[$r['date']] = $r['revenue'];
        }

        return $data;
    }

    /**
     * Finds revenues.
     *
     * @param int            $type
     * @param \DateTime      $from
     * @param \DateTime|null $to
     *
     * @return array
     */
    private function findRevenues($type, \DateTime $from, \DateTime $to = null)
    {
        if ($type === OrderStat::TYPE_DAY) {
            if (null === $to) {
                $from = (clone $from)->modify('first day of this month');
                $to = (clone $from)->modify('last day of this month');
            }
            $interval = new \DateInterval('P1D');
            $format = 'Y-m-d';
        } elseif ($type === OrderStat::TYPE_MONTH) {
            if (null === $to) {
                $from = (clone $from)->modify('first day of january ' . $from->format('Y'));
                $to = (clone $from)->modify('last day of december ' . $from->format('Y'));
            }
            $interval = new \DateInterval('P1M');
            $format = 'Y-m';
        } else {
            throw new InvalidArgumentException("Unexpected order stat type.");
        }

        $result = $this
            ->getRevenueQuery()
            ->setParameters([
                'type' => $type,
                'from' => $from->format($format),
                'to'   => $to->format($format),
            ])
            ->getScalarResult();

        $data = [];
        foreach ($result as $r) {
            $data[$r['date']] = $r['revenue'];
        }

        $period = new \DatePeriod($from, $interval, $to);

        /** @var \DateTime $d */
        foreach ($period as $d) {
            $index = $d->format($format);
            if (!isset($data[$index])) {
                $data[$index] = 0;
            };
        }
        ksort($data);

        return $data;
    }

    /**
     * Returns the revenues query.
     *
     * @return \Doctrine\ORM\AbstractQuery
     */
    private function getRevenueQuery()
    {
        if (null !== $this->revenueQuery) {
            return $this->revenueQuery;
        }

        $qb = $this->createQueryBuilder('o');
        $expr = $qb->expr();

        return $this->revenueQuery = $qb
            ->select(['o.date', 'o.revenue'])
            ->andWhere($expr->eq('o.type', ':type'))
            ->andWhere($expr->gte('o.date', ':from'))
            ->andWhere($expr->lte('o.date', ':to'))
            ->addOrderBy('o.date')
            ->getQuery();
    }
}
