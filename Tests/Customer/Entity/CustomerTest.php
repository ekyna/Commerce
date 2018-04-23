<?php

namespace Ekyna\Component\Commerce\Tests\Customer\Entity;

use Ekyna\Component\Commerce\Customer\Entity\Customer;
use PHPUnit\Framework\TestCase;

/**
 * Class CustomerTest
 * @package Ekyna\Component\Commerce\Tests\Customer\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerTest extends TestCase
{
    public function test_setParent_withCustomer()
    {
        $customer = new Customer();
        $parent = new Customer();

        $customer->setParent($parent);

        $this->assertEquals($parent, $customer->getParent());
        $this->assertTrue($parent->hasChild($customer));
    }

    public function test_setParent_withNull()
    {
        $customer = new Customer();
        $parent = new Customer();

        $customer->setParent($parent);
        $customer->setParent(null);

        $this->assertNull($customer->getParent());
        $this->assertFalse($parent->hasChild($customer));
    }

    public function test_setParent_withAnotherCustomer()
    {
        $customer = new Customer();
        $parentA = new Customer();
        $parentB = new Customer();

        $customer->setParent($parentA);
        $customer->setParent($parentB);

        $this->assertEquals($parentB, $customer->getParent());
        $this->assertTrue($parentB->hasChild($customer));
        $this->assertFalse($parentA->hasChild($customer));
    }
}