<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Cart\Model\CartInterface;
use Ekyna\Component\Commerce\Cart\Repository\CartRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;

/**
 * Class CartRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method CartInterface|null findOneById($id)
 * @method CartInterface|null findOneByKey($key)
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
     * @inheritDoc
     */
    public function findExpired()
    {
        $qb = $this->createQueryBuilder('c');

        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        return $qb
            ->andWhere($qb->expr()->lt('c.expiresAt', ':today'))
            ->andWhere($qb->expr()->lte('c.paidTotal', 0))
            ->getQuery()
            ->setParameter('today', $today)
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
