<?php

namespace Ekyna\Component\Commerce\Tests\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Entity\Cart;
use Ekyna\Component\Commerce\Cart\Entity\CartItem;
use PHPUnit\Framework\TestCase;

/**
 * Class CartItemTest
 * @package Ekyna\Component\Commerce\Tests\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemTest extends TestCase
{
    public function test_setCart_withCart()
    {
        $item = new CartItem();
        $cart = new Cart();

        $item->setCart($cart);

        $this->assertEquals($cart, $item->getCart());
        $this->assertTrue($cart->hasItem($item));
    }

    public function test_setCart_withNull()
    {
        $item = new CartItem();
        $cart = new Cart();

        $item->setCart($cart);
        $item->setCart(null);

        $this->assertEquals(null, $item->getCart());
        $this->assertFalse($cart->hasItem($item));
    }

    public function test_setCart_withAnotherCart()
    {
        $item = new CartItem();
        $cartA = new Cart();
        $cartB = new Cart();

        $item->setCart($cartA);
        $item->setCart($cartB);

        $this->assertEquals($cartB, $item->getCart());
        $this->assertTrue($cartB->hasItem($item));
        $this->assertFalse($cartA->hasItem($item));
    }

    public function test_setParent_withItem()
    {
        $item = new CartItem();
        $parent = new CartItem();

        $item->setParent($parent);

        $this->assertEquals($parent, $item->getParent());
        $this->assertTrue($parent->hasChild($item));
    }

    public function test_setParent_withNull()
    {
        $item = new CartItem();
        $parent = new CartItem();

        $item->setParent($parent);
        $item->setParent(null);

        $this->assertEquals(null, $item->getParent());
        $this->assertFalse($parent->hasChild($item));
    }

    public function test_setParent_withAnotherItem()
    {
        $item = new CartItem();
        $parentA = new CartItem();
        $parentB = new CartItem();

        $item->setParent($parentA);
        $item->setParent($parentB);

        $this->assertEquals($parentB, $item->getParent());
        $this->assertTrue($parentB->hasChild($item));
        $this->assertFalse($parentA->hasChild($item));
    }

    public function test_createChild()
    {
        $item = new CartItem();

        $this->assertInstanceOf(CartItem::class, $item->createChild());
    }
}