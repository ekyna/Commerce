<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Model;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\ItemCostCalculatorInterface;
use Ekyna\Component\Commerce\Common\Calculator\MarginCalculator;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentCostCalculatorInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AbstractMarginTest
 * @package Ekyna\Component\Commerce\Tests\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractMarginTest extends TestCase
{
    protected ShipmentCostCalculatorInterface|MockObject|null $shipmentCostCalculator;
    protected ItemCostCalculatorInterface|MockObject|null     $itemCostCalculator;
    protected AmountCalculatorInterface|MockObject|null       $amountCalculator;
    protected AmountCalculatorFactory|MockObject|null         $amountCalculatorFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->itemCostCalculator = $this->createMock(ItemCostCalculatorInterface::class);
        $this->shipmentCostCalculator = $this->createMock(ShipmentCostCalculatorInterface::class);
        $this->amountCalculator = $this->createMock(AmountCalculatorInterface::class);

        $this->amountCalculatorFactory = $this->createMock(AmountCalculatorFactory::class);
        $this->amountCalculatorFactory
            ->method('create')
            ->willReturn($this->amountCalculator);
    }

    protected function tearDown(): void
    {
        $this->amountCalculatorFactory = null;
        $this->amountCalculator = null;
        $this->shipmentCostCalculator = null;
        $this->itemCostCalculator = null;

        parent::tearDown();
    }

    protected function assertMargin(Margin $actual, Margin $expected): void
    {
        self::assertEquals(
            $expected->getRevenueProduct(),
            $actual->getRevenueProduct(),
            'Unexpected product revenue'
        );
        self::assertEquals(
            $expected->getRevenueShipment(),
            $actual->getRevenueShipment(),
            'Unexpected shipment revenue'
        );
        self::assertEquals(
            $expected->getCostProduct(),
            $actual->getCostProduct(),
            'Unexpected product cost'
        );
        self::assertEquals(
            $expected->getCostSupply(),
            $actual->getCostSupply(),
            'Unexpected supply cost'
        );
        self::assertEquals(
            $expected->getCostShipment(),
            $actual->getCostShipment(),
            'Unexpected shipment cost'
        );
        self::assertEquals(
            $expected->isAverage(),
            $actual->isAverage()
        );
    }
}
