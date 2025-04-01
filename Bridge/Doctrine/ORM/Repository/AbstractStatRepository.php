<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stat\Model\StatInterface;
use Ekyna\Component\Commerce\Stat\Repository\StatRepositoryInterface;

use Ekyna\Component\Resource\Model\DateRange;

use function date;
use function ksort;

/**
 * Class AbstractStatRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractStatRepository extends EntityRepository implements StatRepositoryInterface
{
    private ?Query $revenueQuery = null;

    public function findOneByDay(DateTime $date): ?StatInterface
    {
        return $this->findOneBy([
            'type' => StatInterface::TYPE_DAY,
            'date' => $date->format('Y-m-d'),
        ]);
    }

    public function findOneByMonth(DateTime $date): ?StatInterface
    {
        return $this->findOneBy([
            'type' => StatInterface::TYPE_MONTH,
            'date' => $date->format('Y-m'),
        ]);
    }

    public function findOneByYear(string $year): ?StatInterface
    {
        return $this->findOneBy([
            'type' => StatInterface::TYPE_YEAR,
            'date' => $year,
        ]);
    }

    /**
     * Finds revenues.
     */
    public function findRevenues(int $type, DateRange $range): array
    {
        if ($type === StatInterface::TYPE_DAY) {
            $interval = new DateInterval('P1D');
            $format = 'Y-m-d';
        } elseif ($type === StatInterface::TYPE_MONTH) {
            $interval = new DateInterval('P1M');
            $format = 'Y-m';
        } elseif ($type === StatInterface::TYPE_YEAR) {
            $interval = new DateInterval('P1Y');
            $format = 'Y';
        } else {
            throw new InvalidArgumentException('Unexpected order stat type.');
        }

        $result = $this
            ->getRevenueQuery()
            ->setParameters([
                'type' => $type,
                'from' => $range->getStart()->format($format),
                'to'   => $range->getEnd()->format($format),
            ])
            ->getScalarResult();

        $data = $this->buildRevenueData($result);

        $period = new DatePeriod($range->getStart(), $interval, $range->getEnd());

        /** @var DateTime $d */
        foreach ($period as $d) {
            $index = $d->format($format);
            if (!isset($data[$index])) {
                $data[$index] = '0';
            }
        }
        ksort($data);

        return $data;
    }

    /**
     * Builds the revenue data.
     */
    private function buildRevenueData(array $result): array
    {
        $data = [];

        foreach ($result as $r) {
            $data[$r['date']] = $r['revenue'];
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
            ->select(['o.date', 'o.revenue'])
            ->andWhere($expr->eq('o.type', ':type'))
            ->andWhere($expr->gte('o.date', ':from'))
            ->andWhere($expr->lte('o.date', ':to'))
            ->addOrderBy('o.date')
            ->getQuery();
    }
}
