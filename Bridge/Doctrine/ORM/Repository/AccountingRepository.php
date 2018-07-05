<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Accounting\Repository\AccountingRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class AccountingRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AccountingRepository extends ResourceRepository implements AccountingRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findByTypes(array $types)
    {
        $qb = $this->createQueryBuilder('a');

        return $qb
            ->andWhere($qb->expr()->in('a.type', ':types'))
            ->getQuery()
            ->setParameter('types', $types)
            ->getResult();
    }
}
