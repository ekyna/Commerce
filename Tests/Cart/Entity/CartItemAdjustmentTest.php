<?php

namespace Ekyna\Component\Commerce\Tests\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Entity\CartItem;
use Ekyna\Component\Commerce\Cart\Entity\CartItemAdjustment;
use PHPUnit\Framework\TestCase;

/**
 * Class CartItemAdjustmentTest
 * @package Ekyna\Component\Commerce\Tests\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemAdjustmentTest extends TestCase
{
    public function test_setItem_withItem()
    {
        $adjustment = new CartItemAdjustment();
        $item = new CartItem();

        $adjustment->setItem($item);

        $this->assertEquals($item, $adjustment->getItem());
        $this->assertTrue($item->hasAdjustment($adjustment));
    }

    public function test_setItem_withNull()
    {
        $adjustment = new CartItemAdjustment();
        $item = new CartItem();

        $adjustment->setItem($item);
        $adjustment->setItem(null);

        $this->assertEquals(null, $adjustment->getItem());
        $this->assertFalse($item->hasAdjustment($adjustment));
    }

    public function test_setItem_withAnotherItem()
    {
        $adjustment = new CartItemAdjustment();
        $itemA = new CartItem();
        $itemB = new CartItem();

        $adjustment->setItem($itemA);
        $adjustment->setItem($itemB);

        $this->assertEquals($itemB, $adjustment->getItem());
        $this->assertTrue($itemB->hasAdjustment($adjustment));
        $this->assertFalse($itemA->hasAdjustment($adjustment));
    }
}