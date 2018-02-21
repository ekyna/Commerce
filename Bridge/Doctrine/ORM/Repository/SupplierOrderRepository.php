<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\DBAL\Types\Type;
use Ekyna\Component\Commerce\Supplier\Model\SupplierInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;
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
    public function findNewBySupplier(SupplierInterface $supplier)
    {
        return $this
            ->getFindNewBySupplierQuery()
            ->setParameter('supplier', $supplier)
            ->getResult();
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
    public function getCarriersExpiredDue()
    {
        return $this->getExpiredDue('forwarder');
    }

    /**
     * @inheritDoc
     */
    public function getCarriersFallDue()
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

        $qb = $this->createQueryBuilder('so');

        return $this->findNewBySupplierQuery = $qb
            ->andWhere($qb->expr()->eq('so.supplier', ':supplier'))
            ->andWhere($qb->expr()->eq('so.state', ':state'))
            ->getQuery()
            ->setParameter('state', SupplierOrderStates::STATE_NEW);
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
        $qb = $this->createQueryBuilder('so');
        $ex = $qb->expr();

        $query = $qb
            ->select('SUM(so.' . $prefix . 'Total)')
            ->andWhere($ex->isNull('so.' . $prefix . 'Date'))
            ->andWhere($ex->andX(
                $ex->isNotNull('so.' . $prefix . 'DueDate'),
                $ex->lt('so.' . $prefix . 'DueDate', ':today')
            ))
            ->getQuery()
            ->useQueryCache(true);

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        return $query
            ->setParameter('today', $today, Type::DATETIME)
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
        $qb = $this->createQueryBuilder('so');
        $ex = $qb->expr();

        $query = $qb
            ->select('SUM(so.' . $prefix . 'Total)')
            ->andWhere($ex->isNull('so.' . $prefix . 'Date'))
            ->andWhere($ex->orX(
                $ex->isNull('so.' . $prefix . 'DueDate'),
                $ex->gte('so.' . $prefix . 'DueDate', ':today')
            ))
            ->getQuery()
            ->useQueryCache(true);

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        return $query
            ->setParameter('today', $today, Type::DATETIME)
            ->getSingleScalarResult();
    }
}
