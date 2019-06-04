<?php

namespace Ekyna\Component\Commerce\Tests\Supplier\Entity;

use Ekyna\Component\Commerce\Supplier\Entity\SupplierDelivery;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierDeliveryItem;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder;
use PHPUnit\Framework\TestCase;

/**
 * Class SupplierDeliveryTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryTest extends TestCase
{
    public function test_setOrder_withOrder(): void
    {
        $delivery = new SupplierDelivery();
        $order = new SupplierOrder();

        $delivery->setOrder($order);

        $this->assertEquals($order, $delivery->getOrder());
        $this->assertTrue($order->hasDelivery($delivery));
    }

    public function test_setOrder_withNull(): void
    {
        $delivery = new SupplierDelivery();
        $order = new SupplierOrder();

        $delivery->setOrder($order);
        $delivery->setOrder(null);

        $this->assertNull($delivery->getOrder());
        $this->assertFalse($order->hasDelivery($delivery));
    }

    public function test_setOrder_withAnotherOrder(): void
    {
        $delivery = new SupplierDelivery();
        $orderA = new SupplierOrder();
        $orderB = new SupplierOrder();

        $delivery->setOrder($orderA);
        $delivery->setOrder($orderB);

        $this->assertEquals($orderB, $delivery->getOrder());
        $this->assertTrue($orderB->hasDelivery($delivery));
        $this->assertFalse($orderA->hasDelivery($delivery));
    }

    public function test_addItem(): void
    {
        $delivery = new SupplierDelivery();
        $item = new SupplierDeliveryItem();

        $delivery->addItem($item);

        $this->assertEquals($delivery, $item->getDelivery());
        $this->assertTrue($delivery->hasItem($item));
    }

    public function test_removeItem(): void
    {
        $delivery = new SupplierDelivery();
        $item = new SupplierDeliveryItem();

        $delivery->addItem($item);
        $delivery->removeItem($item);

        $this->assertNull($item->getDelivery());
        $this->assertFalse($delivery->hasItem($item));
    }
}