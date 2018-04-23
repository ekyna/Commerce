<?php

namespace Ekyna\Component\Commerce\Tests\Supplier\Entity;

use Ekyna\Component\Commerce\Supplier\Entity\SupplierDelivery;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierDeliveryItem;
use PHPUnit\Framework\TestCase;


/**
 * Class SupplierDeliveryItemTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemTest extends TestCase
{
    public function test_setDelivery_withDelivery()
    {
        $item = new SupplierDeliveryItem();
        $delivery = new SupplierDelivery();

        $item->setDelivery($delivery);

        $this->assertEquals($delivery, $item->getDelivery());
        $this->assertTrue($delivery->hasItem($item));
    }

    public function test_setDelivery_withNull()
    {
        $item = new SupplierDeliveryItem();
        $delivery = new SupplierDelivery();

        $item->setDelivery($delivery);
        $item->setDelivery(null);

        $this->assertNull($item->getDelivery());
        $this->assertFalse($delivery->hasItem($item));
    }

    public function test_setDelivery_withAnotherDelivery()
    {
        $item = new SupplierDeliveryItem();
        $deliveryA = new SupplierDelivery();
        $deliveryB = new SupplierDelivery();

        $item->setDelivery($deliveryA);
        $item->setDelivery($deliveryB);

        $this->assertEquals($deliveryB, $item->getDelivery());
        $this->assertTrue($deliveryB->hasItem($item));
        $this->assertFalse($deliveryA->hasItem($item));
    }
}