<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\OrderItem;
use Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderItemAdjustmentTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAdjustmentTest extends TestCase
{
    public function test_setItem_withItem()
    {
        $adjustment = new OrderItemAdjustment();
        $item = new OrderItem();

        $adjustment->setItem($item);

        $this->assertEquals($item, $adjustment->getItem());
        $this->assertTrue($item->hasAdjustment($adjustment));
    }

    public function test_setItem_withNull()
    {
        $adjustment = new OrderItemAdjustment();
        $item = new OrderItem();

        $adjustment->setItem($item);
        $adjustment->setItem(null);

        $this->assertEquals(null, $adjustment->getItem());
        $this->assertFalse($item->hasAdjustment($adjustment));
    }

    public function test_setItem_withAnotherItem()
    {
        $adjustment = new OrderItemAdjustment();
        $itemA = new OrderItem();
        $itemB = new OrderItem();

        $adjustment->setItem($itemA);
        $adjustment->setItem($itemB);

        $this->assertEquals($itemB, $adjustment->getItem());
        $this->assertTrue($itemB->hasAdjustment($adjustment));
        $this->assertFalse($itemA->hasAdjustment($adjustment));
    }
}