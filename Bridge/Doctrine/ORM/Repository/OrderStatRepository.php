<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
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
    public function findDayRevenuesByMonth(\DateTime $date, $detailed = false)
    {
        return $this->findRevenues(OrderStat::TYPE_DAY, $date, null, $detailed);
    }

    /**
     * @inheritDoc
     */
    public function findMonthRevenuesByYear(\DateTime $date, $detailed = false)
    {
        return $this->findRevenues(OrderStat::TYPE_MONTH, $date, null, $detailed);
    }

    /**
     * @inheritDoc
     */
    public function findYearRevenues($limit = 8, $detailed = false)
    {
        $qb = $this->createQueryBuilder('o');
        $expr = $qb->expr();

        $result = $qb
            ->select(['o.date', 'o.revenue', 'o.details'])
            ->andWhere($expr->eq('o.type', ':type'))
            ->addOrderBy('o.date')
            ->getQuery()
            ->setParameter('type', OrderStat::TYPE_YEAR)
            ->setMaxResults($limit = 8)
            ->getScalarResult();

        return $this->buildRevenueData($result, $detailed);
    }

    /**
     * Finds revenues.
     *
     * @param int            $type
     * @param \DateTime      $from
     * @param \DateTime|null $to
     * @param bool           $detailed
     *
     * @return array
     */
    private function findRevenues($type, \DateTime $from, \DateTime $to = null, $detailed = false)
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

        $data = $this->buildRevenueData($result, $detailed);

        $period = new \DatePeriod($from, $interval, $to);

        $defaults = $detailed ? [] : 0;
        if ($detailed) {
            foreach (SaleSources::getSources() as $source) {
                $defaults[$source] = 0;
            }
        }

        /** @var \DateTime $d */
        foreach ($period as $d) {
            $index = $d->format($format);
            if (!isset($data[$index])) {
                $data[$index] = $defaults;
            };
        }
        ksort($data);

        return $data;
    }

    /**
     * Builds the revenue data.
     *
     * @param array $result
     * @param bool  $detailed
     *
     * @return array
     */
    private function buildRevenueData(array $result, $detailed = false)
    {
        $data = [];

        foreach ($result as $r) {
            $data[$r['date']] = $detailed ? json_decode($r['details'], true) : $r['revenue'];
        }

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
            ->select(['o.date', 'o.revenue', 'o.details'])
            ->andWhere($expr->eq('o.type', ':type'))
            ->andWhere($expr->gte('o.date', ':from'))
            ->andWhere($expr->lte('o.date', ':to'))
            ->addOrderBy('o.date')
            ->getQuery();
    }
}
