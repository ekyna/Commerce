<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\OrderShipment;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentParcel;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderShipmentParcelTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentParcelTest extends TestCase
{
    public function test_setShipment_withShipment()
    {
        $parcel = new OrderShipmentParcel();
        $shipment = new OrderShipment();

        $parcel->setShipment($shipment);

        $this->assertEquals($shipment, $parcel->getShipment());
        $this->assertTrue($shipment->hasParcel($parcel));
    }

    public function test_setShipment_withNull()
    {
        $parcel = new OrderShipmentParcel();
        $shipment = new OrderShipment();

        $parcel->setShipment($shipment);
        $parcel->setShipment(null);

        $this->assertNull($parcel->getShipment());
        $this->assertFalse($shipment->hasParcel($parcel));
    }

    public function test_setShipment_withAnotherShipment()
    {
        $parcel = new OrderShipmentParcel();
        $shipmentA = new OrderShipment();
        $shipmentB = new OrderShipment();

        $parcel->setShipment($shipmentA);
        $parcel->setShipment($shipmentB);

        $this->assertEquals($shipmentB, $parcel->getShipment());
        $this->assertTrue($shipmentB->hasParcel($parcel));
        $this->assertFalse($shipmentA->hasParcel($parcel));
    }
}