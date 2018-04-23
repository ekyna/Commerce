<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderInvoice;
use Ekyna\Component\Commerce\Order\Entity\OrderShipment;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentItem;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentLabel;
use Ekyna\Component\Commerce\Order\Entity\OrderShipmentParcel;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderShipmentTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentTest extends TestCase
{
    public function test_setOrder_withOrder()
    {
        $shipment = new OrderShipment();
        $order = new Order();

        $shipment->setOrder($order);

        $this->assertEquals($order, $shipment->getOrder());
        $this->assertTrue($order->hasShipment($shipment));
    }

    public function test_setOrder_withNull()
    {
        $shipment = new OrderShipment();
        $order = new Order();

        $shipment->setOrder($order);
        $shipment->setOrder(null);

        $this->assertEquals(null, $shipment->getOrder());
        $this->assertFalse($order->hasShipment($shipment));
    }

    public function test_setOrder_withAnotherOrder()
    {
        $shipment = new OrderShipment();
        $orderA = new Order();
        $orderB = new Order();

        $shipment->setOrder($orderA);
        $shipment->setOrder($orderB);

        $this->assertEquals($orderB, $shipment->getOrder());
        $this->assertTrue($orderB->hasShipment($shipment));
        $this->assertFalse($orderA->hasShipment($shipment));
    }
    
    public function test_setInvoice_withInvoice()
    {
        $shipment = new OrderShipment();
        $invoice = new OrderInvoice();

        $shipment->setInvoice($invoice);

        $this->assertEquals($invoice, $shipment->getInvoice());
        $this->assertEquals($shipment, $invoice->getShipment());
    }

    public function test_setInvoice_withNull()
    {
        $shipment = new OrderShipment();
        $invoice = new OrderInvoice();

        $shipment->setInvoice($invoice);
        $shipment->setInvoice(null);

        $this->assertNull($shipment->getInvoice());
        $this->assertNull($invoice->getShipment());
    }

    public function test_setInvoice_withAnotherInvoice()
    {
        $shipment = new OrderShipment();
        $invoiceA = new OrderInvoice();
        $invoiceB = new OrderInvoice();

        $shipment->setInvoice($invoiceA);
        $shipment->setInvoice($invoiceB);

        $this->assertEquals($invoiceB, $shipment->getInvoice());
        $this->assertEquals($shipment, $invoiceB->getShipment());
        $this->assertNull($invoiceA->getShipment());
    }

    public function test_addItem()
    {
        $shipment = new OrderShipment();
        $item = new OrderShipmentItem();

        $shipment->addItem($item);

        $this->assertEquals($shipment, $item->getShipment());
        $this->assertTrue($shipment->hasItem($item));
    }

    public function test_removeItem()
    {
        $shipment = new OrderShipment();
        $item = new OrderShipmentItem();

        $shipment->addItem($item);
        $shipment->removeItem($item);

        $this->assertNull($item->getShipment());
        $this->assertFalse($shipment->hasItem($item));
    }

    public function test_addParcel()
    {
        $shipment = new OrderShipment();
        $parcel = new OrderShipmentParcel();

        $shipment->addParcel($parcel);

        $this->assertEquals($shipment, $parcel->getShipment());
        $this->assertTrue($shipment->hasParcel($parcel));
    }

    public function test_removeParcel()
    {
        $shipment = new OrderShipment();
        $parcel = new OrderShipmentParcel();

        $shipment->addParcel($parcel);
        $shipment->removeParcel($parcel);

        $this->assertNull($parcel->getShipment());
        $this->assertFalse($shipment->hasParcel($parcel));
    }

    public function test_addLabel()
    {
        $shipment = new OrderShipment();
        $label = new OrderShipmentLabel();

        $shipment->addLabel($label);

        $this->assertEquals($shipment, $label->getShipment());
        $this->assertTrue($shipment->hasLabel($label));
    }

    public function test_removeLabel()
    {
        $shipment = new OrderShipment();
        $label = new OrderShipmentLabel();

        $shipment->addLabel($label);
        $shipment->removeLabel($label);

        $this->assertNull($label->getShipment());
        $this->assertFalse($shipment->hasLabel($label));
    }
}