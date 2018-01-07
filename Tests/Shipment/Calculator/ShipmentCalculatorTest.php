<?php

namespace Ekyna\Component\Commerce\Tests\Shipment\Resolver;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCalculator;
use Ekyna\Component\Commerce\Tests\BaseTestCase;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;

/**
 * Class ShipmentCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentCalculatorTest extends BaseTestCase
{
    /**
     * @var ShipmentCalculator
     */
    private $shipmentCalculator;

    /**
     * @var InvoiceCalculatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceCalculator;


    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->invoiceCalculator = $this->createMock(InvoiceCalculatorInterface::class);

        $this->shipmentCalculator = new ShipmentCalculator(
            $this->getSubjectHelperMock()
        );

        $this->shipmentCalculator->setInvoiceCalculator($this->invoiceCalculator);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->shipmentCalculator = null;
        $this->invoiceCalculator = null;
    }

    /**
     * @covers ShipmentCalculator::calculateAvailableQuantity()
     */
    public function test_calculate_available()
    {
        $order = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);

        $shipmentItem = Fixtures::createShipmentItem(Fixtures::createShipment($order), $orderItem);

        $subject = Fixtures::createProduct();

        $this
            ->getSubjectHelperMock()
            ->method('resolve')
            ->with($orderItem)
            ->willReturn($subject);

//        $stockUnit = Fixtures::createStockUnit($subject, null, 20, 20, 10);
//        $stockAssignment = Fixtures::createStockAssignment($stockUnit, $orderItem, 10);
//        $this->assertEquals(10, $this->shipmentCalculator->calculateAvailableQuantity($shipmentItem));
//
//        $stockUnit->setSoldQuantity(20)->setShippedQuantity(10);
//        $this->assertEquals(10, $this->shipmentCalculator->calculateAvailableQuantity($shipmentItem));

        $suA = Fixtures::createStockUnit($subject, null, 20, 20, 5);
        $saA = Fixtures::createStockAssignment($suA, $orderItem, 5);
        $suB = Fixtures::createStockUnit($subject, null, 20, 0, 5);
        $saB = Fixtures::createStockAssignment($suB, $orderItem, 5);
        $this->assertEquals(5, $this->shipmentCalculator->calculateAvailableQuantity($shipmentItem));
    }
}