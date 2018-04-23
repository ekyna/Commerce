<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderAdjustment;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderAdjustmentTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAdjustmentTest extends TestCase
{
    public function test_setOrder_withOrder()
    {
        $adjustment = new OrderAdjustment();
        $order = new Order();

        $adjustment->setOrder($order);

        $this->assertEquals($order, $adjustment->getOrder());
        $this->assertTrue($order->hasAdjustment($adjustment));
    }

    public function test_setOrder_withNull()
    {
        $adjustment = new OrderAdjustment();
        $order = new Order();

        $adjustment->setOrder($order);
        $adjustment->setOrder(null);

        $this->assertEquals(null, $adjustment->getOrder());
        $this->assertFalse($order->hasAdjustment($adjustment));
    }

    public function test_setOrder_withAnotherOrder()
    {
        $adjustment = new OrderAdjustment();
        $orderA = new Order();
        $orderB = new Order();

        $adjustment->setOrder($orderA);
        $adjustment->setOrder($orderB);

        $this->assertEquals($orderB, $adjustment->getOrder());
        $this->assertTrue($orderB->hasAdjustment($adjustment));
        $this->assertFalse($orderA->hasAdjustment($adjustment));
    }
}