<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use DateTime;
use Decimal\Decimal;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class SupplierOrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderRepository extends ResourceRepository implements SupplierOrderRepositoryInterface
{
    private ?Query $findNewBySupplierQuery = null;

    public function findNewBySupplier(Model\SupplierInterface $supplier): array
    {
        return $this
            ->getFindNewBySupplierQuery()
            ->setParameter('supplier', $supplier)
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
            ->setParameter('states', [Model\SupplierOrderStates::STATE_NEW, Model\SupplierOrderStates::STATE_ORDERED]);
    }

    /**
     * Returns the expired due orders.
     *
     * @return array<Model\SupplierOrderInterface>
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
     * @return array<Model\SupplierOrderInterface>
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
        $total = $this
            ->getExpiredDueQueryBuilder($prefix)
            ->select('SUM(' . $this->getAlias() . '.' . $prefix . 'Total)')
            ->getQuery()
            ->getSingleScalarResult();

        return new Decimal($total ?: 0);
    }

    /**
     * Returns the fall due total.
     */
    private function getFallDue(string $prefix): Decimal
    {
        $total = $this
            ->getFallDueQueryBuilder($prefix)
            ->select('SUM(' . $this->getAlias() . '.' . $prefix . 'Total)')
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
            ->andWhere($ex->gt($as . '.' . $prefix . 'Total', 0))
            ->andWhere($ex->isNull($as . '.' . $prefix . 'Date'))
            ->andWhere($ex->isNotNull($as . '.' . $prefix . 'DueDate'))
            ->andWhere($ex->lt($as . '.' . $prefix . 'DueDate', ':today'))
            ->andWhere($ex->neq($as . '.state', ':state'))
            ->setParameter('today', (new DateTime())->setTime(0, 0), Types::DATETIME_MUTABLE)
            ->setParameter('state', Model\SupplierOrderStates::STATE_CANCELED)
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
            ->andWhere($ex->gt($as . '.' . $prefix . 'Total', 0))
            ->andWhere($ex->isNull($as . '.' . $prefix . 'Date'))
            ->andWhere($ex->orX(
                $ex->isNull($as . '.' . $prefix . 'DueDate'),
                $ex->gte($as . '.' . $prefix . 'DueDate', ':today')
            ))
            ->andWhere($ex->neq($as . '.state', ':state'))
            ->setParameter('today', (new DateTime())->setTime(0, 0), Types::DATETIME_MUTABLE)
            ->setParameter('state', Model\SupplierOrderStates::STATE_CANCELED)
            ->addOrderBy($as . '.' . $prefix . 'DueDate', 'ASC');
    }

    protected function getAlias(): string
    {
        return 'so';
    }
}
