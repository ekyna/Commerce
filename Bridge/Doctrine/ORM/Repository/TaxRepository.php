<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;

/**
 * Class TaxRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TaxRepository extends ResourceRepository implements TaxRepositoryInterface
{
    private ?Query $byCodeQuery = null;


    /**
     * @inheritDoc
     */
    public function findOneByCode(string $code): ?TaxInterface
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return $this
            ->getByCodeQuery()
            ->setParameter('code', $code)
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find one by code" query.
     *
     * @return Query
     */
    private function getByCodeQuery(): Query
    {
        if ($this->byCodeQuery) {
            return $this->byCodeQuery;
        }

        $qb = $this->createQueryBuilder('t');

        return $this->byCodeQuery = $qb
            ->andWhere($qb->expr()->eq('t.code', ':code'))
            ->setMaxResults(1)
            ->getQuery()
            ->useQueryCache(true);
    }
}
