<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class CouponRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CouponRepository extends ResourceRepository implements CouponRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByCode(string $code): ?CouponInterface
    {
        $qb = $this->createQueryBuilder('c');

        return $qb
            ->andWhere($qb->expr()->eq('c.code', ':code'))
            ->getQuery()
            ->setParameter('code', $code)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByCustomer(CustomerInterface $customer, bool $unused = true): array
    {
        $qb = $this->createQueryBuilder('c');

        $qb->andWhere($qb->expr()->eq('c.customer', ':customer'));

        if ($unused) {
            $qb->andWhere($qb->expr()->lt('c.usage', 'c.limit'));
        }

        return $qb
            ->getQuery()
            ->setParameter('customer', $customer)
            ->getResult();
    }
}
