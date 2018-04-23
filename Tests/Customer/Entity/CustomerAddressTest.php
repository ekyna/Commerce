<?php

namespace Ekyna\Component\Commerce\Tests\Customer\Entity;

use Ekyna\Component\Commerce\Customer\Entity\Customer;
use Ekyna\Component\Commerce\Customer\Entity\CustomerAddress;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerAddressTest
 * @package Ekyna\Component\Commerce\Tests\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressTest extends TestCase
{
    public function test_setCustomer_withCustomer()
    {
        $address = new CustomerAddress();
        $customer = new Customer();

        $address->setCustomer($customer);

        $this->assertEquals($customer, $address->getCustomer());
        $this->assertTrue($customer->hasAddress($address));
    }

    public function test_setCustomer_withNull()
    {
        $address = new CustomerAddress();
        $customer = new Customer();

        $address->setCustomer($customer);
        $address->setCustomer(null);

        $this->assertNull($address->getCustomer());
        $this->assertFalse($customer->hasAddress($address));
    }

    public function test_setCustomer_withAnotherCustomer()
    {
        $address = new CustomerAddress();
        $customerA = new Customer();
        $customerB = new Customer();

        $address->setCustomer($customerA);
        $address->setCustomer($customerB);

        $this->assertEquals($customerB, $address->getCustomer());
        $this->assertTrue($customerB->hasAddress($address));
        $this->assertFalse($customerA->hasAddress($address));
    }
}