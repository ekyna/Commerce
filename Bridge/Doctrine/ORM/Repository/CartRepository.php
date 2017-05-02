<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class CartRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartRepository extends AbstractSaleRepository implements CartRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findLatestByCustomer(CustomerInterface $customer)
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->andWhere($qb->expr()->eq('c.customer', ':customer'))
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->setMaxResults(1)
            ->setParameter('customer', $customer)
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    protected function getAlias()
    {
        return 'c';
    }
}
