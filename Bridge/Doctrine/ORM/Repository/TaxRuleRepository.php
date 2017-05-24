<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
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
     * @var \Doctrine\ORM\Query
     */
    private $byCountryAndCustomerQuery;

    /**
     * @var \Doctrine\ORM\Query
     */
    private $byCountryAndBusinessQuery;


    /**
     * @inheritdoc
     */
    public function findOneByCountryForCustomer(
        CountryInterface $country
    ) {
        return $this
            ->getByCountryAndCustomerQuery()
            ->setParameter('country', $country)
            ->getOneOrNullResult();
    }


    /**
     * @inheritdoc
     */
    public function findOneByCountryForBusiness(
        CountryInterface $country
    ) {
        return $this
            ->getByCountryAndBusinessQuery()
            ->setParameter('country', $country)
            ->getOneOrNullResult();
    }

    /**
     * Returns the "find one by country and customer" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getByCountryAndCustomerQuery()
    {
        if (null === $this->byCountryAndCustomerQuery) {
            $qb = $this->getBaseQueryBuilder();

            $this->byCountryAndCustomerQuery = $qb
                ->andWhere($qb->expr()->eq('r.customer', ':customer'))
                ->getQuery()
                ->setParameter('customer', true)
                ->setMaxResults(1);
        }

        return $this->byCountryAndCustomerQuery;
    }

    /**
     * Returns the "find one by country and business" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getByCountryAndBusinessQuery()
    {
        if (null === $this->byCountryAndBusinessQuery) {
            $qb = $this->getBaseQueryBuilder();

            $this->byCountryAndBusinessQuery = $qb
                ->andWhere($qb->expr()->eq('r.business', ':business'))
                ->getQuery()
                ->setParameter('business', true)
                ->setMaxResults(1);
        }

        return $this->byCountryAndBusinessQuery;
    }

    /**
     * Returns the base query builder.
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getBaseQueryBuilder()
    {
        $qb = $this->getQueryBuilder('r', 'r.id');

        return $qb
            ->select('r', 't')
            ->join('r.taxes', 't')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isMemberOf(':country', 'r.countries'),
                    'r.countries IS EMPTY'
                )
            )
            ->addOrderBy('r.priority', 'DESC');
    }
}
