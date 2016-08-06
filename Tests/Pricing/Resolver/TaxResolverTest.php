<?php

namespace Ekyna\Component\Commerce\Tests\Pricing\Resolver;

use Ekyna\Component\Commerce\Customer\Entity\CustomerAddress;
use Ekyna\Component\Commerce\Pricing\Entity\TaxGroup;
use Ekyna\Component\Commerce\Pricing\Resolver\TaxResolver;
use Ekyna\Component\Commerce\Tests\OrmTestCase;

/**
 * Class TaxResolverTest
 * @package Ekyna\Component\Commerce\Tests\Pricing\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @coversDefaultClass \Ekyna\Component\Commerce\Pricing\Resolver\TaxResolver
 */
class TaxResolverTest extends OrmTestCase
{
    /**
     * @test
     * @covers ::getApplicableTaxesByTaxGroupAndCustomerGroups
     */
    public function itThrowsRuntimeExceptionIfOriginAddressIsNotSet()
    {
        $this->expectException(\RuntimeException::class);

        $resolver = $this->createTaxResolver(TaxResolver::BY_ORIGIN);

        $resolver->getApplicableTaxesByTaxGroupAndCustomerGroups(new TaxGroup(), []);
    }

    /**
     * @test
     * @covers ::getApplicableTaxesBySubjectAndCustomer
     */
    public function itResolvesTaxesBySubjectAndCustomer()
    {
        /** @var \Ekyna\Component\Commerce\Subject\Model\SubjectInterface $subject */
        $subject = $this->find('subject', 1); // Regular tax group
        /** @var \Ekyna\Component\Commerce\Customer\Model\CustomerInterface $customer */
        $customer = $this->find('customer', 1); // Final customers
        /** @var \Ekyna\Component\Commerce\Common\Model\CountryInterface $country */
        $country = $this->find('country', 1); // FR
        $address = $this->createAddress($country);

        $resolver = $this->createTaxResolver(TaxResolver::BY_ORIGIN);
        $resolver->setOriginAddress($address);

        $result = $resolver->getApplicableTaxesBySubjectAndCustomer($subject, $customer);

        $expected = array($this->find('tax', 1));

        $this->assertEquals($expected, $result);
    }

    /**
     * @test
     * @covers ::getApplicableTaxesByTaxGroupAndCustomerGroups
     */
    public function itResolvesTaxesByTaxGroupAndCustomerGroupsAndOrigin()
    {
        $cases = [
            // Regular tax rule / Final customers / Origin FR => TVA 20%
            'Case 1' => [
                'tax_group_id'      => 1,    // Regular tax group
                'customer_group_id' => 1,    // Final customers
                'origin_country_id' => 1,    // Origin FR
                'tax_ids'           => [1],  // TVA 20%
            ],
            // Regular tax rule / Final customers / Origin US => US-CA 8.25%
            'Case 2' => [
                'tax_group_id'      => 1,    // Regular tax group
                'customer_group_id' => 1,    // Final customers
                'origin_country_id' => 2,    // Origin US
                'tax_ids'           => [3],  // US-CA 8.25%
            ],
            // Reduced tax rule / Final customers / Origin FR => TVA 10%
            'Case 3' => [
                'tax_group_id'      => 2,    // Reduced tax group
                'customer_group_id' => 1,    // Final customers
                'origin_country_id' => 1,    // Origin FR
                'tax_ids'           => [2],  // TVA 10%
            ],
            // Reduced tax rule / Final customers / Origin US => US-CA 8.25%
            'Case 4' => [
                'tax_group_id'      => 2,    // Reduced tax group
                'customer_group_id' => 1,    // Final customers
                'origin_country_id' => 2,    // Origin US
                'tax_ids'           => [3],  // US-CA 8.25%
            ],
            // Regular tax rule / Final customers / Origin FR / Address US => TVA 20%
            'Case 5' => [
                'tax_group_id'       => 1,    // Regular tax group
                'customer_group_id'  => 1,    // Final customers
                'origin_country_id'  => 1,    // Origin FR
                'address_country_id' => 2,    // Address US
                'tax_ids'            => [1],  // TVA 20%
            ],
            // Reduced tax rule / Final customers / Origin US / Address FR => US-CA 8.25%
            'Case 6' => [
                'tax_group_id'       => 2,    // Reduced tax group
                'customer_group_id'  => 1,    // Final customers
                'origin_country_id'  => 1,    // Origin US
                'address_country_id' => 2,    // Address FR
                'tax_ids'            => [2],  // US-CA 8.25%
            ],
            // Regular tax rule / Resellers / Origin FR => none
            'Case 7' => [
                'tax_group_id'      => 1,    // Regular tax group
                'customer_group_id' => 2,    // Resellers
                'origin_country_id' => 1,    // Origin FR
                'tax_ids'           => [],   // none
            ],
            // Reduced tax rule / Resellers / Origin US / Address FR => none
            'Case 8' => [
                'tax_group_id'       => 2,    // Reduced tax group
                'customer_group_id'  => 2,    // Resellers
                'origin_country_id'  => 2,    // Origin US
                'address_country_id' => 1,    // Address FR
                'tax_ids'            => [],   // none
            ],
            // TODO test on multiple customers groups ?
        ];

        $resolver = $this->createTaxResolver(TaxResolver::BY_ORIGIN);

        foreach ($cases as $name => $case) {
            /** @var \Ekyna\Component\Commerce\Common\Model\CountryInterface $country */
            $country = $this->find('country', $case['origin_country_id']);
            $originAddress = $this->createAddress($country);
            $resolver->setOriginAddress($originAddress);

            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface $taxGroup */
            $taxGroup = $this->find('taxGroup', $case['tax_group_id']);
            $customerGroups = [$this->find('customerGroup', $case['customer_group_id'])];

            $address = null;
            if (array_key_exists('address_country_id', $case)) {
                /** @var \Ekyna\Component\Commerce\Common\Model\CountryInterface $country */
                $country = $this->find('country', $case['address_country_id']);
                $address = $this->createAddress($country);
            }

            $result = $resolver->getApplicableTaxesByTaxGroupAndCustomerGroups(
                $taxGroup, $customerGroups, $address
            );

            $expected = [];
            foreach ($case['tax_ids'] as $taxId) {
                array_push($expected, $this->find('tax', $taxId));
            }

            $this->assertEquals($expected, $result, "Test '$name' failed.");
        }
    }

    /**
     * @test
     * @covers ::getApplicableTaxesByTaxGroupAndCustomerGroups
     */
    public function itResolvesTaxesByTaxGroupAndCustomerGroupsAndAddress()
    {
        $cases = [
            // Regular tax rule / Final customers / Origin FR => TVA 20%
            'Case 1' => [
                'tax_group_id'       => 1,    // Regular tax group
                'customer_group_id'  => 1,    // Final customers
                'address_country_id' => 1,    // Address FR
                'tax_ids'            => [1],  // TVA 20%
            ],
            // Regular tax rule / Final customers / Origin US => US-CA 8.25%
            'Case 2' => [
                'tax_group_id'       => 1,    // Regular tax group
                'customer_group_id'  => 1,    // Final customers
                'address_country_id' => 2,    // Address US
                'tax_ids'            => [3],  // US-CA 8.25%
            ],
            // Reduced tax rule / Final customers / Origin FR => TVA 10%
            'Case 3' => [
                'tax_group_id'       => 2,    // Reduced tax group
                'customer_group_id'  => 1,    // Final customers
                'address_country_id' => 1,    // Address FR
                'tax_ids'            => [2],  // TVA 10%
            ],
            // Reduced tax rule / Final customers / Origin US => US-CA 8.25%
            'Case 4' => [
                'tax_group_id'       => 2,    // Reduced tax group
                'customer_group_id'  => 1,    // Final customers
                'address_country_id' => 2,    // Address US
                'tax_ids'            => [3],  // US-CA 8.25%
            ],
            // Regular tax rule / Resellers / Origin FR => none
            'Case 7' => [
                'tax_group_id'       => 1,    // Regular tax group
                'customer_group_id'  => 2,    // Resellers
                'address_country_id' => 1,    // Address FR
                'tax_ids'            => [],   // none
            ],
            // TODO test on multiple customers groups ?
        ];

        $resolver = $this->createTaxResolver(TaxResolver::BY_DELIVERY);

        foreach ($cases as $name => $case) {

            /** @var \Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface $taxGroup */
            $taxGroup = $this->find('taxGroup', $case['tax_group_id']);
            $customerGroups = [$this->find('customerGroup', $case['customer_group_id'])];

            /** @var \Ekyna\Component\Commerce\Common\Model\CountryInterface $country */
            $country = $this->find('country', $case['address_country_id']);
            $address = $this->createAddress($country);

            $result = $resolver->getApplicableTaxesByTaxGroupAndCustomerGroups(
                $taxGroup, $customerGroups, $address
            );

            $expected = [];
            foreach ($case['tax_ids'] as $taxId) {
                array_push($expected, $this->find('tax', $taxId));
            }

            $this->assertEquals($expected, $result, "Test '$name' failed.");
        }
    }

    private function createTaxResolver($mode)
    {
        /** @var \Ekyna\Component\Commerce\Pricing\Repository\TaxRuleRepositoryInterface $taxRuleRepository */
        $taxRuleRepository = $this->getRepository('taxRule');

        return new TaxResolver($taxRuleRepository, $mode);
    }

    /**
     * Create an address mock.
     *
     * @param \Ekyna\Component\Commerce\Common\Model\CountryInterface $country
     *
     * @return \Ekyna\Component\Commerce\Common\Model\AddressInterface
     */
    private function createAddress($country)
    {
        $address = new CustomerAddress();
        return $address->setCountry($country);
    }
}
