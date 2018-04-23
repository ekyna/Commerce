<?php

namespace Ekyna\Component\Commerce\Tests\Supplier\Entity;

use Ekyna\Component\Commerce\Supplier\Entity\SupplierDelivery;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderAttachment;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderItem;
use PHPUnit\Framework\TestCase;

/**
 * Class SupplierOrderTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderTest extends TestCase
{
    public function test_addItem()
    {
        $order = new SupplierOrder();
        $item = new SupplierOrderItem();

        $order->addItem($item);

        $this->assertEquals($order, $item->getOrder());
        $this->assertTrue($order->hasItem($item));
    }

    public function test_removeItem()
    {
        $order = new SupplierOrder();
        $item = new SupplierOrderItem();

        $order->addItem($item);
        $order->removeItem($item);

        $this->assertNull($item->getOrder());
        $this->assertFalse($order->hasItem($item));
    }
    
    public function test_addDelivery()
    {
        $order = new SupplierOrder();
        $delivery = new SupplierDelivery();

        $order->addDelivery($delivery);

        $this->assertEquals($order, $delivery->getOrder());
        $this->assertTrue($order->hasDelivery($delivery));
    }

    public function test_removeDelivery()
    {
        $order = new SupplierOrder();
        $delivery = new SupplierDelivery();

        $order->addDelivery($delivery);
        $order->removeDelivery($delivery);

        $this->assertNull($delivery->getOrder());
        $this->assertFalse($order->hasDelivery($delivery));
    }
    
    public function test_addAttachment()
    {
        $order = new SupplierOrder();
        $attachment = new SupplierOrderAttachment();

        $order->addAttachment($attachment);

        $this->assertEquals($order, $attachment->getSupplierOrder());
        $this->assertTrue($order->hasAttachment($attachment));
    }

    public function test_removeAttachment()
    {
        $order = new SupplierOrder();
        $attachment = new SupplierOrderAttachment();

        $order->addAttachment($attachment);
        $order->removeAttachment($attachment);

        $this->assertNull($attachment->getSupplierOrder());
        $this->assertFalse($order->hasAttachment($attachment));
    }
}