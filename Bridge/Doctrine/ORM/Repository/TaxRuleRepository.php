<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class TaxRuleRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleRepository extends ResourceRepository implements TaxRuleRepositoryInterface
{
    /**
     * @var Query
     */
    private $byCodeQuery;

    /**
     * @var Query
     */
    private $forCustomerQuery;

    /**
     * @var Query
     */
    private $forBusinessQuery;


    /**
     * @inheritDoc
     */
    public function findOneByCode(string $code): ?TaxRuleInterface
    {
        return $this
            ->getByCodeQuery()
            ->setParameter('code', $code)
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function findOneForCustomer(CountryInterface $source, CountryInterface $target): ?TaxRuleInterface
    {
        return $this
            ->getForCustomerQuery()
            ->setParameter('source', $source)
            ->setParameter('target', $target)
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function findOneForBusiness(CountryInterface $source, CountryInterface $target): ?TaxRuleInterface
    {
        return $this
            ->getForBusinessQuery()
            ->setParameter('source', $source)
            ->setParameter('target', $target)
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

        $qb = $this->createQueryBuilder('r');

        return $this->byCodeQuery = $qb
            ->andWhere($qb->expr()->eq('r.code', ':code'))
            ->getQuery()
            ->useQueryCache(true);
    }

    /**
     * Returns the "find one for customer" query.
     *
     * @return Query
     */
    private function getForCustomerQuery()
    {
        if (null === $this->forCustomerQuery) {
            $qb = $this->getBaseQueryBuilder();

            $this->forCustomerQuery = $qb
                ->andWhere($qb->expr()->eq('r.customer', ':customer'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameter('customer', true)
                ->setMaxResults(1);
        }

        return $this->forCustomerQuery;
    }

    /**
     * Returns the "find one for business" query.
     *
     * @return Query
     */
    private function getForBusinessQuery(): Query
    {
        if (null === $this->forBusinessQuery) {
            $qb = $this->getBaseQueryBuilder();

            $this->forBusinessQuery = $qb
                ->andWhere($qb->expr()->eq('r.business', ':business'))
                ->getQuery()
                ->useQueryCache(true)
                ->setParameter('business', true)
                ->setMaxResults(1);
        }

        return $this->forBusinessQuery;
    }

    /**
     * Returns the base query builder.
     *
     * @return QueryBuilder
     */
    private function getBaseQueryBuilder(): QueryBuilder
    {
        $qb = $this->getQueryBuilder('r', 'r.id');

        return $qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isMemberOf(':source', 'r.sources'),
                    'r.sources IS EMPTY'
                )
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isMemberOf(':target', 'r.targets'),
                    'r.targets IS EMPTY'
                )
            )
            ->addOrderBy('r.priority', 'DESC');
    }
}
