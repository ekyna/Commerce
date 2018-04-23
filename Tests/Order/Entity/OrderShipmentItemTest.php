<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\OrderShipment;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentItem;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderShipmentItemTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItemTest extends TestCase
{
    public function test_setShipment_withShipment()
    {
        $item = new OrderShipmentItem();
        $shipment = new OrderShipment();

        $item->setShipment($shipment);

        $this->assertEquals($shipment, $item->getShipment());
        $this->assertTrue($shipment->hasItem($item));
    }

    public function test_setShipment_withNull()
    {
        $item = new OrderShipmentItem();
        $shipment = new OrderShipment();

        $item->setShipment($shipment);
        $item->setShipment(null);

        $this->assertNull($item->getShipment());
        $this->assertFalse($shipment->hasItem($item));
    }

    public function test_setShipment_withAnotherShipment()
    {
        $item = new OrderShipmentItem();
        $shipmentA = new OrderShipment();
        $shipmentB = new OrderShipment();

        $item->setShipment($shipmentA);
        $item->setShipment($shipmentB);

        $this->assertEquals($shipmentB, $item->getShipment());
        $this->assertTrue($shipmentB->hasItem($item));
        $this->assertFalse($shipmentA->hasItem($item));
    }
}