<?php

namespace Ekyna\Component\Commerce\Tests\Bridge\Doctrine\ORM\Repository;

use Ekyna\Component\Commerce\Tests\OrmTestCase;

/**
 * Class TaxRuleRepositoryTest
 * @package Ekyna\Component\Commerce\Tests\Bridge\Doctrine\ORM\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @coversDefaultClass \Ekyna\Component\Commerce\Bridge\Doctrine\ORM\Repository\TaxRuleRepository
 */
class TaxRuleRepositoryTest extends OrmTestCase
{
    /**
     * @covers ::findByTaxGroupAndCustomerGroups
     */
    public function testFindByTaxGroupAndCustomerGroups()
    {
        $cases = [
            'Case 1' => [
                'tax_group_id' => 1,
                'customer_id'  => 1,
                'address_id'   => 1,
                'tax_rule_ids' => [1],
            ],
            'Case 2' => [
                'tax_group_id' => 2,
                'customer_id'  => 1,
                'address_id'   => 1,
                'tax_rule_ids' => [2],
            ],
            'Case 3' => [
                'tax_group_id' => 1,
                'customer_id'  => 2,
                'address_id'   => 2,
                'tax_rule_ids' => [3],
            ],
            'Case 4' => [
                'tax_group_id' => 2,
                'customer_id'  => 2,
                'address_id'   => 2,
                'tax_rule_ids' => [3],
            ],
        ];

        foreach ($cases as $name => $case) {
            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface $taxGroup */
            $taxGroup = $this->find('taxGroup', $case['tax_group_id']);
            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
            $customer = $this->find('customer', $case['customer_id']);
            /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerAddressInterface $address */
            $address = $this->find('customerAddress', $case['address_id']);

            $expectedResult = [];
            foreach ($case['tax_rule_ids'] as $taxRuleId) {
                array_push($expectedResult, $this->find('taxRule', $taxRuleId));
            }

            /** @var \Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface $repository */
            $repository = $this->getResourceRepository('taxRule');
            $result = $repository->findByTaxGroupAndCustomerGroupAndCountry(
                $taxGroup,
                $customer->getCustomerGroups()->toArray(),
                $address
            );

            $this->assertEquals($expectedResult, $result, "Test '$name' failed.");
        }
    }
}
