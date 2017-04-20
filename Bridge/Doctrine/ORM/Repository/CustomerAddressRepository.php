<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerAddressRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class CustomerAddressRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressRepository extends ResourceRepository implements CustomerAddressRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByCustomerAndParents(CustomerInterface $customer): array
    {
        $qb = $this->getCollectionQueryBuilder('a', 'a.id');

        $qb->andWhere($qb->expr()->in('a.customer', ':customers'));

        $customers = [$customer];
        if ($customer->hasParent()) {
            $customers[] = $customer->getParent();
        }

        return $qb
            ->groupBy('a.id')
            ->addOrderBy('a.invoiceDefault', 'DESC')
            ->addOrderBy('a.deliveryDefault', 'DESC')
            ->addOrderBy('a.id', 'DESC')
            ->getQuery()
            ->useQueryCache(true)
            ->setParameter('customers', $customers)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findByCustomer(CustomerInterface $customer, CustomerAddressInterface $exclude = null): array
    {
        $qb = $this->getCollectionQueryBuilder('a', 'a.id');
        $qb
            ->orWhere($qb->expr()->eq('a.customer', ':customer'))
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
     * @inheritDoc
     */
    protected function getAlias(): string
    {
        return 'a';
    }
}
