<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
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
    private $byTaxGroupAndCustomerGroupsQuery;


    /**
     * @inheritdoc
     */
    public function findByTaxGroupAndCustomerGroupsAndCountry(
        TaxGroupInterface $taxGroup,
        array $customerGroups,
        CountryInterface $country
    ) {
        if (empty($customerGroups)) {
            throw new \InvalidArgumentException('Expected non empty array customer groups parameter.');
        } else {
            foreach ($customerGroups as $group) {
                if (!$group instanceof CustomerGroupInterface) {
                    throw new \InvalidArgumentException('Expected instances of Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface.');
                }
            }
        }

        return $this->getByTaxGroupAndCustomerGroupsQuery()
            ->setParameters([
                'tax_group'       => $taxGroup,
                'customer_groups' => $customerGroups,
                'country'         => $country,
            ])
            ->getResult();
    }

    /**
     * Returns the "find by tax group and customer groups" query.
     *
     * @return \Doctrine\ORM\Query
     */
    private function getByTaxGroupAndCustomerGroupsQuery()
    {
        if (null === $this->byTaxGroupAndCustomerGroupsQuery) {
            $qb = $this->getQueryBuilder();
            $this->byTaxGroupAndCustomerGroupsQuery = $qb
                ->leftJoin('o.taxes', 't')
                ->andWhere($qb->expr()->isMemberOf(':tax_group', 'o.taxGroups'))
                ->andWhere($qb->expr()->isMemberOf(':customer_groups', 'o.customerGroups'))
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNull('t.id'),
                        $qb->expr()->eq('t.country', ':country')
                    )
                )
                ->addOrderBy('o.priority', 'DESC')
                ->addGroupBy('o.id')
                ->getQuery();
        }

        return $this->byTaxGroupAndCustomerGroupsQuery;
    }
}
