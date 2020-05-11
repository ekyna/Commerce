<?php

namespace Ekyna\Component\Commerce\Tests\Shipment\Calculator;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculator;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ShipmentSubjectCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Shipment\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectCalculatorTest extends TestCase
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
        Fixture::shipmentItem([
            'shipment' => [
                'order' => $order = Fixture::order()
            ],
            'item'     => $orderItem = Fixture::orderItem([
                'order'    => $order,
                'quantity' => 10,
            ]),
        ]);

        $subject = Fixture::subject();

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

        $suA = Fixture::stockUnit([
            'subject'  => $subject,
            'ordered'  => 20,
            'received' => 20,
            'sold'     => 5,
        ]);
        $saA = Fixture::stockAssignment([
            'unit' => $suA,
            'item' => $orderItem,
            'sold' => 5,
        ]);
        $suB = Fixture::stockUnit([
            'subject' => $subject,
            'ordered' => 20,
            'sold'    => 5,
        ]);
        $saB = Fixture::stockAssignment([
            'unit' => $suB,
            'item' => $orderItem,
            'sold' => 5,
        ]);
        $this->assertEquals(5, $this->shipmentCalculator->calculateAvailableQuantity($orderItem));
    }
}
