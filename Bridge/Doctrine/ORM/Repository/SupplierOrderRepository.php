<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

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
}
