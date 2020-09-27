<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Newsletter\Model\MemberInterface;
use Ekyna\Component\Commerce\Newsletter\Repository\MemberRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class MemberRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MemberRepository extends ResourceRepository implements MemberRepositoryInterface
{
    /**
     * @var Query
     */
    private $findOneByEmailQuery;


    /**
     * @inheritDoc
     */
    public function findOneByEmail(string $email): ?MemberInterface
    {
        return $this
            ->getFindOneByEmailQuery()
            ->setParameter('email', $email)
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find one by email" query.
     *
     * @return Query
     */
    private function getFindOneByEmailQuery(): Query
    {
        if ($this->findOneByEmailQuery) {
            return $this->findOneByEmailQuery;
        }

        $qb = $this->createQueryBuilder('m');

        return $this->findOneByEmailQuery = $qb
            ->andWhere($qb->expr()->eq('m.email', ':email'))
            ->getQuery()
            ->setMaxResults(1);
    }
}
