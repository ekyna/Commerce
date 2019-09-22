<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CouponInterface;
use Ekyna\Component\Commerce\Common\Repository\CouponRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

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
}
