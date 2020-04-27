<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Query;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxGroupRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class TaxGroupRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroupRepository extends ResourceRepository implements TaxGroupRepositoryInterface
{
    /**
     * @var TaxGroupInterface
     */
    private $defaultTaxGroup;

    /**
     * @var Query
     */
    private $byCodeQuery;


    /**
     * @inheritdoc
     */
    public function findDefault(bool $throwException = true): ?TaxGroupInterface
    {
        if (null !== $this->defaultTaxGroup) {
            return $this->defaultTaxGroup;
        }

        if (null === $this->defaultTaxGroup = $this->findOneBy(['default' => true])) {
            if ($throwException) {
                throw new RuntimeException('Default tax group not found.');
            }

            return null;
        }

        return $this->defaultTaxGroup;
    }

    /**
     * @inheritDoc
     */
    public function findOneByCode(string $code): ?TaxGroupInterface
    {
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

        $qb = $this->createQueryBuilder('g');

        return $this->byCodeQuery = $qb
            ->andWhere($qb->expr()->eq('g.code', ':code'))
            ->getQuery()
            ->useQueryCache(true);
    }
}
