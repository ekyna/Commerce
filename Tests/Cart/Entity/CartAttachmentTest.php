<?php

namespace Ekyna\Component\Commerce\Tests\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Entity\Cart;
use Ekyna\Component\Commerce\Cart\Entity\CartAttachment;
use PHPUnit\Framework\TestCase;

/**
 * Class CartAttachmentTest
 * @package Ekyna\Component\Commerce\Tests\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAttachmentTest extends TestCase
{
    public function test_setCart_withCart()
    {
        $attachment = new CartAttachment();
        $cart = new Cart();

        $attachment->setCart($cart);

        $this->assertEquals($cart, $attachment->getCart());
        $this->assertTrue($cart->hasAttachment($attachment));
    }

    public function test_setCart_withNull()
    {
        $attachment = new CartAttachment();
        $cart = new Cart();

        $attachment->setCart($cart);
        $attachment->setCart(null);

        $this->assertEquals(null, $attachment->getCart());
        $this->assertFalse($cart->hasAttachment($attachment));
    }

    public function test_setCart_withAnotherCart()
    {
        $attachment = new CartAttachment();
        $cartA = new Cart();
        $cartB = new Cart();

        $attachment->setCart($cartA);
        $attachment->setCart($cartB);

        $this->assertEquals($cartB, $attachment->getCart());
        $this->assertTrue($cartB->hasAttachment($attachment));
        $this->assertFalse($cartA->hasAttachment($attachment));
    }
}