<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderAddress;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderAddressTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddressTest extends TestCase
{
    public function test_setInvoiceOrder_withOrder()
    {
        $address = new OrderAddress();
        $order = new Order();

        $address->setInvoiceOrder($order);

        $this->assertEquals($order, $address->getInvoiceOrder());
        $this->assertEquals($address, $order->getInvoiceAddress());
    }

    public function test_setInvoiceOrder_withNull()
    {
        $address = new OrderAddress();
        $order = new Order();

        $address->setInvoiceOrder($order);
        $address->setInvoiceOrder(null);

        $this->assertNull($address->getInvoiceOrder());
        $this->assertNull($order->getInvoiceAddress());
    }

    public function test_setInvoiceOrder_withAnotherOrder()
    {
        $address = new OrderAddress();
        $orderA = new Order();
        $orderB = new Order();

        $address->setInvoiceOrder($orderA);
        $address->setInvoiceOrder($orderB);

        $this->assertEquals($orderB, $address->getInvoiceOrder());
        $this->assertEquals($address, $orderB->getInvoiceAddress());
        $this->assertNull($orderA->getInvoiceAddress());
    }

    public function test_setDeliveryOrder_withOrder()
    {
        $address = new OrderAddress();
        $order = new Order();

        $address->setDeliveryOrder($order);

        $this->assertEquals($order, $address->getDeliveryOrder());
        $this->assertEquals($address, $order->getDeliveryAddress());
    }

    public function test_setDeliveryOrder_withNull()
    {
        $address = new OrderAddress();
        $order = new Order();

        $address->setDeliveryOrder($order);
        $address->setDeliveryOrder(null);

        $this->assertNull($address->getDeliveryOrder());
        $this->assertNull($order->getDeliveryAddress());
    }

    public function test_setDeliveryOrder_withAnotherOrder()
    {
        $address = new OrderAddress();
        $orderA = new Order();
        $orderB = new Order();

        $address->setDeliveryOrder($orderA);
        $address->setDeliveryOrder($orderB);

        $this->assertEquals($orderB, $address->getDeliveryOrder());
        $this->assertEquals($address, $orderB->getDeliveryAddress());
        $this->assertNull($orderA->getDeliveryAddress());
    }
}