<?php

namespace Ekyna\Component\Commerce\Tests\Customer;

use Ekyna\Component\Commerce\Customer\Entity\Customer;
use Ekyna\Component\Commerce\Tests\OrmTestCase;

/**
 * Class CustomerManagerTest
 * @package Ekyna\Component\Commerce\Tests\Customer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerManagerTest extends OrmTestCase
{
    public function testCustomerCreation()
    {
        $em = $this->getEntityManager();

        $customer = new Customer();
        $customer
            ->setFirstName('Test')
            ->setLastName('Test')
            ->setEmail('test.test@example.org');

        $em->persist($customer);
        $em->flush();

        $this->assertTrue(is_numeric($customer->getId()));
    }

    public function testFindCustomerByEmail()
    {
        /** @var \Ekyna\Component\Commerce\Customer\Entity\CustomerGroup $group */
        $group = $this->getCustomerGroupRepository()->find(1);
        /** @var \Ekyna\Component\Commerce\Customer\Entity\Customer $customer */
        $customer = $this->getCustomerRepository()->find(1);

        $this->assertNotNull($group);
        $this->assertNotNull($customer);
        $this->assertTrue($customer->hasCustomerGroup($group));
    }

    private function getCustomerRepository()
    {
        return $this
            ->getEntityManager()
            ->getRepository('Ekyna\Component\Commerce\Customer\Entity\Customer');
    }

    private function getCustomerGroupRepository()
    {
        return $this
            ->getEntityManager()
            ->getRepository('Ekyna\Component\Commerce\Customer\Entity\CustomerGroup');
    }
}
