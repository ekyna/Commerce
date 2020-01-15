<?php

namespace Ekyna\Component\Commerce\Tests\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolver;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use Ekyna\Component\Commerce\Tests\Stock\StockTestCase;

/**
 * Class StockUnitStateResolverTest
 * @package Ekyna\Component\Commerce\Tests\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitStateResolverTest extends StockTestCase
{
    /**
     * @covers StockUnitStateResolver::resolve()
     */
    public function test_resolve_newState(): void
    {
        $unit = Fixtures::createStockUnit();

        $this->assertEquals(StockUnitStates::STATE_NEW, $unit->getState());
        $this->assertNull($unit->getClosedAt());
    }

    /**
     * @covers StockUnitStateResolver::resolve()
     */
    public function test_resolve_pendingState_withoutReceivedQuantity(): void
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10);

        $this->assertEquals(StockUnitStates::STATE_PENDING, $unit->getState());
        $this->assertNull($unit->getClosedAt());
    }

    /**
     * @covers StockUnitStateResolver::resolve()
     */
    public function test_resolve_pendingState_withReceivedQuantity(): void
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 5, 5, 5);

        $this->assertEquals(StockUnitStates::STATE_PENDING, $unit->getState());
        $this->assertNull($unit->getClosedAt());
    }

    /**
     * @covers StockUnitStateResolver::resolve()
     */
    public function test_resolve_readyState(): void
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10);

        $this->assertEquals(StockUnitStates::STATE_READY, $unit->getState());
        $this->assertNull($unit->getClosedAt());
    }

    /**
     * @covers StockUnitStateResolver::resolve()
     */
    public function test_resolve_closedState(): void
    {
        $unit = Fixtures::createStockUnit(null, Fixtures::createSupplierOrderItem(), 10, 10, 10, 10);

        $this->assertEquals(StockUnitStates::STATE_CLOSED, $unit->getState());
        $this->assertNotNull($unit->getClosedAt());
    }
}
