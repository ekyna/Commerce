<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateTime;
use DateTimeInterface;
use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use Ekyna\Component\Resource\Model\DateRange;

/**
 * Class SupplierOrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderRepository extends ResourceRepository implements SupplierOrderRepositoryInterface
{
    private ?Query $findNewBySupplierQuery = null;
    private ?Query $findByOrderedAtQuery   = null;

    public function findNewBySupplier(SupplierInterface $supplier): array
    {
        return $this
            ->getFindNewBySupplierQuery()
            ->setParameter('supplier', $supplier)
            ->getResult();
    }

    public function findByOrderAt(DateRange $range, int $page, int $size): array
    {
        return $this
            ->getFindByOrderedAtQuery()
            ->setParameter('from', $range->getStart(), Types::DATETIME_MUTABLE)
            ->setParameter('to', $range->getEnd(), Types::DATETIME_MUTABLE)
            ->setFirstResult($size * $page)
            ->setMaxResults($size)
            ->getResult();
    }

    public function findSuppliersExpiredDue(): array
    {
        return $this->findExpiredDue('payment');
    }

    public function findSuppliersFallDue(): array
    {
        return $this->findFallDue('payment');
    }

    public function findForwardersExpiredDue(): array
    {
        return $this->findExpiredDue('forwarder');
    }

    public function findForwardersFallDue(): array
    {
        return $this->findFallDue('forwarder');
    }

    public function getSuppliersExpiredDue(): Decimal
    {
        return $this->getExpiredDue('payment');
    }

    public function getSuppliersFallDue(): Decimal
    {
        return $this->getFallDue('payment');
    }

    public function getForwardersExpiredDue(): Decimal
    {
        return $this->getExpiredDue('forwarder');
    }

    public function getForwardersFallDue(): Decimal
    {
        return $this->getFallDue('forwarder');
    }

    /**
     * Returns the "find new by supplier" query.
     */
    private function getFindNewBySupplierQuery(): ?Query
    {
        if (null !== $this->findNewBySupplierQuery) {
            return $this->findNewBySupplierQuery;
        }

        $qb = $this->createQueryBuilder();
        $as = $this->getAlias();

        return $this->findNewBySupplierQuery = $qb
            ->andWhere($qb->expr()->eq($as . '.supplier', ':supplier'))
            ->andWhere($qb->expr()->in($as . '.state', ':states'))
            ->addOrderBy($as . '.createdAt', 'DESC')
            ->getQuery()
            ->setParameter('states', [SupplierOrderStates::STATE_NEW, SupplierOrderStates::STATE_ORDERED]);
    }

    /**
     * Returns the "find by 'ordered at' date" query.
     */
    private function getFindByOrderedAtQuery(): Query
    {
        if (null !== $this->findByOrderedAtQuery) {
            return $this->findByOrderedAtQuery;
        }

        $qb = $this->createQueryBuilder('o');
        $ex = $qb->expr();

        return $this->findByOrderedAtQuery = $qb
            ->andWhere($ex->in('o.state', ':state'))
            ->andWhere($ex->between('o.orderedAt', ':from', ':to'))
            ->setParameter('state', [
                SupplierOrderStates::STATE_COMPLETED,
                SupplierOrderStates::STATE_PARTIAL,
                SupplierOrderStates::STATE_VALIDATED,
                SupplierOrderStates::STATE_RECEIVED,
                SupplierOrderStates::STATE_ORDERED,
            ])
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * Returns the expired due orders.
     *
     * @return array<int, SupplierOrderInterface>
     */
    private function findExpiredDue(string $prefix): array
    {
        return $this
            ->getExpiredDueQueryBuilder($prefix)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the fall due orders.
     *
     * @return array<int, SupplierOrderInterface>
     */
    private function findFallDue(string $prefix): array
    {
        return $this
            ->getFallDueQueryBuilder($prefix)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the expired due total.
     */
    private function getExpiredDue(string $prefix): Decimal
    {
        $as = $this->getAlias();

        $total = $this
            ->getExpiredDueQueryBuilder($prefix)
            ->select("SUM($as.{$prefix}Total - $as.{$prefix}PaidTotal)")
            ->getQuery()
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    /**
     * Returns the fall due total.
     */
    private function getFallDue(string $prefix): Decimal
    {
        $as = $this->getAlias();

        $total = $this
            ->getFallDueQueryBuilder($prefix)
            ->select("SUM($as.{$prefix}Total - $as.{$prefix}PaidTotal)")
            ->getQuery()
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    /**
     * Returns the expired due total.
     */
    private function getExpiredDueQueryBuilder(string $prefix): QueryBuilder
    {
        $qb = $this->createQueryBuilder();
        $as = $this->getAlias();
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->lt($as . '.' . $prefix . 'PaidTotal', $as . '.' . $prefix . 'Total'))
            ->andWhere($ex->isNotNull($as . '.' . $prefix . 'DueDate'))
            ->andWhere($ex->lt($as . '.' . $prefix . 'DueDate', ':today'))
            ->andWhere($ex->neq($as . '.state', ':state'))
            ->setParameter('today', (new DateTime())->setTime(0, 0), Types::DATETIME_MUTABLE)
            ->setParameter('state', SupplierOrderStates::STATE_CANCELED)
            ->addOrderBy($as . '.' . $prefix . 'DueDate', 'ASC');
    }

    /**
     * Returns the fall due total.
     */
    private function getFallDueQueryBuilder(string $prefix): QueryBuilder
    {
        $qb = $this->createQueryBuilder();
        $as = $this->getAlias();
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->lt($as . '.' . $prefix . 'PaidTotal', $as . '.' . $prefix . 'Total'))
            ->andWhere($ex->orX(
                $ex->isNull($as . '.' . $prefix . 'DueDate'),
                $ex->gte($as . '.' . $prefix . 'DueDate', ':today')
            ))
            ->andWhere($ex->neq($as . '.state', ':state'))
            ->setParameter('today', (new DateTime())->setTime(0, 0), Types::DATETIME_MUTABLE)
            ->setParameter('state', SupplierOrderStates::STATE_CANCELED)
            ->addOrderBy($as . '.' . $prefix . 'DueDate', 'ASC');
    }

    protected function getAlias(): string
    {
        return 'so';
    }
}
