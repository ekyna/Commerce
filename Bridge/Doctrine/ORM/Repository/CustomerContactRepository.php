<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Commerce\Customer\Repository\CustomerContactRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class CustomerContactRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerContactRepository extends ResourceRepository implements CustomerContactRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findByCustomer(CustomerInterface $customer, CustomerAddressInterface $exclude = null): array
    {
        $qb = $this->getCollectionQueryBuilder('c', 'c.id');

        return $qb
            ->orWhere($qb->expr()->eq('c.customer', ':customer'))
            ->addOrderBy('c.id', 'DESC')
            ->getQuery()
            ->useQueryCache(true)
            ->setParameters([
                'customer' => $customer,
            ])
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'c';
    }
}
