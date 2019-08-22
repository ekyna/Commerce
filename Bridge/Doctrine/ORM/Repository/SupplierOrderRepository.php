<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
use Ekyna\Component\Commerce\Supplier\Model;
use Ekyna\Component\Commerce\Supplier\Repository\SupplierOrderRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class SupplierOrderRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderRepository extends ResourceRepository implements SupplierOrderRepositoryInterface
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $findNewBySupplierQuery;


    /**
     * @inheritDoc
     */
    public function findNewBySupplier(Model\SupplierInterface $supplier)
    {
        return $this
            ->getFindNewBySupplierQuery()
            ->setParameter('supplier', $supplier)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findSuppliersExpiredDue()
    {
        return $this->findExpiredDue('payment');
    }

    /**
     * @inheritDoc
     */
    public function findSuppliersFallDue()
    {
        return $this->findFallDue('payment');
    }

    /**
     * @inheritDoc
     */
    public function findForwardersExpiredDue()
    {
        return $this->findExpiredDue('forwarder');
    }

    /**
     * @inheritDoc
     */
    public function findForwardersFallDue()
    {
        return $this->findFallDue('forwarder');
    }

    /**
     * @inheritDoc
     */
    public function getSuppliersExpiredDue()
    {
        return $this->getExpiredDue('payment');
    }

    /**
     * @inheritDoc
     */
    public function getSuppliersFallDue()
    {
        return $this->getFallDue('payment');
    }

    /**
     * @inheritDoc
     */
    public function getForwardersExpiredDue()
    {
        return $this->getExpiredDue('forwarder');
    }

    /**
     * @inheritDoc
     */
    public function getForwardersFallDue()
    {
        return $this->getFallDue('forwarder');
    }

    /**
     * Returns the "find new by supplier" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getFindNewBySupplierQuery()
    {
        if (null !== $this->findNewBySupplierQuery) {
            return $this->findNewBySupplierQuery;
        }

        $qb = $this->createQueryBuilder();
        $as = $this->getAlias();

        return $this->findNewBySupplierQuery = $qb
            ->andWhere($qb->expr()->eq($as . '.supplier', ':supplier'))
            ->andWhere($qb->expr()->eq($as . '.state', ':state'))
            ->getQuery()
            ->setParameter('state', Model\SupplierOrderStates::STATE_NEW);
    }

    /**
     * Returns the expired due orders.
     *
     * @param string $prefix
     *
     * @return Model\SupplierOrderInterface[]
     */
    private function findExpiredDue($prefix)
    {
        return $this
            ->getExpiredDueQueryBuilder($prefix)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the fall due orders.
     *
     * @param string $prefix
     *
     * @return Model\SupplierOrderInterface[]
     */
    private function findFallDue($prefix)
    {
        return $this
            ->getFallDueQueryBuilder($prefix)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the expired due total.
     *
     * @param string $prefix
     *
     * @return float
     */
    private function getExpiredDue($prefix)
    {
        return $this
            ->getExpiredDueQueryBuilder($prefix)
            ->select('SUM(' . $this->getAlias() . '.' . $prefix . 'Total)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the fall due total.
     *
     * @param string $prefix
     *
     * @return float
     */
    private function getFallDue($prefix)
    {
        return $this
            ->getFallDueQueryBuilder($prefix)
            ->select('SUM(' . $this->getAlias() . '.' . $prefix . 'Total)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Returns the expired due total.
     *
     * @param string $prefix
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getExpiredDueQueryBuilder($prefix)
    {
        $qb = $this->createQueryBuilder();
        $as = $this->getAlias();
        $ex = $qb->expr();

        return $qb
            ->andWhere($ex->gt($as . '.' . $prefix . 'Total', 0))
            ->andWhere($ex->isNull($as . '.' . $prefix . 'Date'))
            ->andWhere($ex->andX(
                $ex->isNotNull($as . '.' . $prefix . 'DueDate'),
                $ex->lt($as . '.' . $prefix . 'DueDate', ':today')
            ))
            ->setParameter('today', (new \DateTime())->setTime(0, 0, 0, 0), Type::DATETIME)
            ->addOrderBy($as . '.' . $prefix . 'DueDate', 'ASC');
    }

    /**
     * Returns the fall due total.
     *
     * @param string $prefix
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getFallDueQueryBuilder($prefix)
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
            ->setParameter('today', (new \DateTime())->setTime(0, 0, 0, 0), Type::DATETIME)
            ->addOrderBy($as . '.' . $prefix . 'DueDate', 'ASC');
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'so';
    }
}
