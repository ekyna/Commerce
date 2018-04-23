<?php

namespace Ekyna\Component\Commerce\Tests\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Entity\Cart;
use Ekyna\Component\Commerce\Cart\Entity\CartAdjustment;
use PHPUnit\Framework\TestCase;

/**
 * Class CartAdjustmentTest
 * @package Ekyna\Component\Commerce\Tests\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAdjustmentTest extends TestCase
{
    public function test_setCart_withCart()
    {
        $adjustment = new CartAdjustment();
        $cart = new Cart();

        $adjustment->setCart($cart);

        $this->assertEquals($cart, $adjustment->getCart());
        $this->assertTrue($cart->hasAdjustment($adjustment));
    }

    public function test_setCart_withNull()
    {
        $adjustment = new CartAdjustment();
        $cart = new Cart();

        $adjustment->setCart($cart);
        $adjustment->setCart(null);

        $this->assertEquals(null, $adjustment->getCart());
        $this->assertFalse($cart->hasAdjustment($adjustment));
    }

    public function test_setCart_withAnotherCart()
    {
        $adjustment = new CartAdjustment();
        $cartA = new Cart();
        $cartB = new Cart();

        $adjustment->setCart($cartA);
        $adjustment->setCart($cartB);

        $this->assertEquals($cartB, $adjustment->getCart());
        $this->assertTrue($cartB->hasAdjustment($adjustment));
        $this->assertFalse($cartA->hasAdjustment($adjustment));
    }
}