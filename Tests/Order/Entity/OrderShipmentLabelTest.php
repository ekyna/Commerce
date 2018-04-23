<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\OrderShipment;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentLabel;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderShipmentLabelTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentLabelTest extends TestCase
{
    public function test_setShipment_withShipment()
    {
        $label = new OrderShipmentLabel();
        $shipment = new OrderShipment();

        $label->setShipment($shipment);

        $this->assertEquals($shipment, $label->getShipment());
        $this->assertTrue($shipment->hasLabel($label));
    }

    public function test_setShipment_withNull()
    {
        $label = new OrderShipmentLabel();
        $shipment = new OrderShipment();

        $label->setShipment($shipment);
        $label->setShipment(null);

        $this->assertNull($label->getShipment());
        $this->assertFalse($shipment->hasLabel($label));
    }

    public function test_setShipment_withAnotherShipment()
    {
        $label = new OrderShipmentLabel();
        $shipmentA = new OrderShipment();
        $shipmentB = new OrderShipment();

        $label->setShipment($shipmentA);
        $label->setShipment($shipmentB);

        $this->assertEquals($shipmentB, $label->getShipment());
        $this->assertTrue($shipmentB->hasLabel($label));
        $this->assertFalse($shipmentA->hasLabel($label));
    }
}