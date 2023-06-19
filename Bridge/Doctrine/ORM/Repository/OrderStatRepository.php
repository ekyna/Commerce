<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateInterval;
use DatePeriod;
use DateTime;
use Decimal\Decimal;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Common\Model\SaleSources;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stat\Entity\OrderStat;
use Ekyna\Component\Commerce\Stat\Repository\OrderStatRepositoryInterface;

use function json_decode;

/**
 * Class OrderStatRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStatRepository extends EntityRepository implements OrderStatRepositoryInterface
{
    private ?Query $revenueQuery = null;

    public function findOneByDay(DateTime $date): ?OrderStat
    {
        return $this->findOneBy([
            'type' => OrderStat::TYPE_DAY,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    public function findOneByMonth(DateTime $date): ?OrderStat
    {
        return $this->findOneBy([
            'type' => OrderStat::TYPE_MONTH,
            'date' => $date->format('Y-m'),
        ]);
    }

    public function findOneByYear(DateTime $date): ?OrderStat
    {
        return $this->findOneBy([
            'type' => OrderStat::TYPE_YEAR,
            'date' => $date->format('Y'),
        ]);
    }

    public function findSumByYear(DateTime $date): OrderStat
    {
        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();
        $data = $qb
            ->select([
                'SUM(o.revenue - o.shipping) as revenue',
                'SUM(o.shipping) as shipping',
                'SUM(o.cost) as cost',
                'SUM(o.orders) as orders',
                'SUM(o.items) as items',
                'AVG(o.average) as average',
            ])
            ->andWhere($ex->eq('o.type', ':type'))
            ->andWhere($ex->between('o.date', ':from', ':to'))
            ->setParameters([
                'type' => OrderStat::TYPE_DAY,
                'from' => new DateTime('first day of january ' . $date->format('Y')),
                'to'   => $date,
            ])
            ->getQuery()
            ->getScalarResult();

        $result = new OrderStat();
        $result
            ->setDate($date->format('Y'))
            ->setType(OrderStat::TYPE_YEAR)
            ->loadResult(current($data));

        return $result;
    }

    public function findDayRevenuesByMonth(DateTime $date, bool $detailed = false): array
    {
        return $this->findRevenues(OrderStat::TYPE_DAY, $date, null, $detailed);
    }

    public function findMonthRevenuesByYear(DateTime $date, bool $detailed = false): array
    {
        return $this->findRevenues(OrderStat::TYPE_MONTH, $date, null, $detailed);
    }

    public function findYearRevenues(int $limit = 8, bool $detailed = false): array
    {
        $qb = $this->createQueryBuilder('o');
        $expr = $qb->expr();

        $result = $qb
            ->select(['o.date', 'o.revenue', 'o.details'])
            ->andWhere($expr->eq('o.type', ':type'))
            ->addOrderBy('o.date')
            ->getQuery()
            ->setParameter('type', OrderStat::TYPE_YEAR)
            ->setMaxResults(8)
            ->getScalarResult();

        return $this->buildRevenueData($result, $detailed);
    }

    /**
     * Finds revenues.
     */
    private function findRevenues(int $type, DateTime $from, DateTime $to = null, bool $detailed = false): array
    {
        if ($type === OrderStat::TYPE_DAY) {
            if (null === $to) {
                $from = (clone $from)->modify('first day of this month');
                $to = (clone $from)->modify('last day of this month');
            }
            $interval = new DateInterval('P1D');
            $format = 'Y-m-d';
        } elseif ($type === OrderStat::TYPE_MONTH) {
            if (null === $to) {
                $from = (clone $from)->modify('first day of january ' . $from->format('Y'));
                $to = (clone $from)->modify('last day of december ' . $from->format('Y'));
            }
            $interval = new DateInterval('P1M');
            $format = 'Y-m';
        } else {
            throw new InvalidArgumentException('Unexpected order stat type.');
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

        $period = new DatePeriod($from, $interval, $to);

        $defaults = $detailed ? [] : '0';
        if ($detailed) {
            foreach (SaleSources::getSources() as $source) {
                $defaults[$source] = '0';
            }
        }

        /** @var DateTime $d */
        foreach ($period as $d) {
            $index = $d->format($format);
            if (!isset($data[$index])) {
                $data[$index] = $defaults;
            }
        }
        ksort($data);

        return $data;
    }

    /**
     * Builds the revenue data.
     */
    private function buildRevenueData(array $result, bool $detailed = false): array
    {
        $data = [];

        foreach ($result as $r) {
            $data[$r['date']] = $detailed ? json_decode($r['details'], true) : $r['revenue'];
        }

        return $data;
    }

    /**
     * Returns the revenues query.
     */
    private function getRevenueQuery(): Query
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
