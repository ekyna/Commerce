<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
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
    public function findByCustomerAndParents(CustomerInterface $customer)
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
    public function findByCustomer(CustomerInterface $customer, CustomerAddressInterface $exclude = null)
    {
        $qb = $this->getCollectionQueryBuilder('a', 'a.id');
        $qb
            ->join('a.customer', 'c')
            ->orWhere($qb->expr()->in('a.customer', ':customer'))
            ->groupBy('a.id')
            ->addOrderBy('a.invoiceDefault', 'DESC')
            ->addOrderBy('a.deliveryDefault', 'DESC')
            ->addOrderBy('a.id', 'DESC');

        $parameters = [
            'customer' => $customer,
        ];

        if (null !== $exclude) {
            $qb->andWhere($qb->expr()->neq('a', ':exclude'));
            $parameters['exclude'] = $exclude;
        }

        return $qb
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters($parameters)
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
