<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerAddressRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CustomerAddressRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressRepository extends ResourceRepository implements CustomerAddressRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findByCustomer(CustomerInterface $customer)
    {
        $qb = $this->getCollectionQueryBuilder('a', 'a.id');

        return $qb
            ->join('a.customer', 'c')
            ->leftJoin('c.parent', 'p')
            ->orWhere($qb->expr()->in('a.customer', ':customer'))
            ->orWhere($qb->expr()->in('c.parent', ':customer'))
            ->orWhere($qb->expr()->in('p.parent', ':customer'))
            ->groupBy('a.id')
            ->addOrderBy('a.invoiceDefault', 'DESC')
            ->addOrderBy('a.deliveryDefault', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('customer', $customer)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'a';
    }
}
