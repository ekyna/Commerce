<?php

namespace Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Ekyna\Component\Commerce\Common\Model\AddressInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface;

/**
 * Class TaxRuleRepository
 * @package Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRuleRepository extends EntityRepository implements TaxRuleRepositoryInterface
{
    /**
     * @var \Doctrine\ORM\Query
     */
    private $byTaxGroupAndCustomerGroupsQuery;


    /**
     * @inheritdoc
     */
    public function findByTaxGroupAndCustomerGroups(
        TaxGroupInterface $taxGroup,
        array $customerGroups,
        AddressInterface $address
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
                'country'         => $address->getCountry(),
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
            $qb = $this->createQueryBuilder('rule');
            $this->byTaxGroupAndCustomerGroupsQuery = $qb
                ->leftJoin('rule.taxes', 'tax')
                ->andWhere($qb->expr()->isMemberOf(':tax_group', 'rule.taxGroups'))
                ->andWhere($qb->expr()->isMemberOf(':customer_groups', 'rule.customerGroups'))
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNull('tax.id'),
                        $qb->expr()->eq('tax.country', ':country')
                    )
                )
                ->addOrderBy('rule.priority', 'DESC')
                ->addGroupBy('rule.id')
                ->getQuery();
        }

        return $this->byTaxGroupAndCustomerGroupsQuery;
    }
}
