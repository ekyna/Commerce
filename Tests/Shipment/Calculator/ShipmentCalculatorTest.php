<?php

namespace Ekyna\Component\Commerce\Tests\Shipment\Resolver;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculator;
use Ekyna\Component\Commerce\Tests\TestCase;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ShipmentCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Shipment
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentCalculatorTest extends TestCase
{
    /**
     * @var ShipmentSubjectCalculator
     */
    private $shipmentCalculator;

    /**
     * @var InvoiceSubjectCalculatorInterface|MockObject
     */
    private $invoiceCalculator;


    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->invoiceCalculator = $this->createMock(InvoiceSubjectCalculatorInterface::class);

        $this->shipmentCalculator = new ShipmentSubjectCalculator(
            $this->getSubjectHelperMock()
        );

        $this->shipmentCalculator->setInvoiceCalculator($this->invoiceCalculator);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->shipmentCalculator = null;
        $this->invoiceCalculator = null;
    }

    /**
     * @covers ShipmentSubjectCalculator::calculateAvailableQuantity()
     */
    public function test_calculate_available()
    {
        $order = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);

        Fixtures::createShipmentItem(Fixtures::createShipment($order), $orderItem);

        $subject = Fixtures::createSubject();

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
        $this->assertEquals(5, $this->shipmentCalculator->calculateAvailableQuantity($orderItem));
    }
}
