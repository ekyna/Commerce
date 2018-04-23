<?php

namespace Ekyna\Component\Commerce\Tests\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Entity\Cart;
use Ekyna\Component\Commerce\Cart\Entity\CartPayment;
use PHPUnit\Framework\TestCase;

/**
 * Class CartPaymentTest
 * @package Ekyna\Component\Commerce\Tests\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartPaymentTest extends TestCase
{
    public function test_setCart_withCart()
    {
        $payment = new CartPayment();
        $cart = new Cart();

        $payment->setCart($cart);

        $this->assertEquals($cart, $payment->getCart());
        $this->assertTrue($cart->hasPayment($payment));
    }

    public function test_setCart_withNull()
    {
        $payment = new CartPayment();
        $cart = new Cart();

        $payment->setCart($cart);
        $payment->setCart(null);

        $this->assertEquals(null, $payment->getCart());
        $this->assertFalse($cart->hasPayment($payment));
    }

    public function test_setCart_withAnotherCart()
    {
        $payment = new CartPayment();
        $cartA = new Cart();
        $cartB = new Cart();

        $payment->setCart($cartA);
        $payment->setCart($cartB);

        $this->assertEquals($cartB, $payment->getCart());
        $this->assertTrue($cartB->hasPayment($payment));
        $this->assertFalse($cartA->hasPayment($payment));
    }
}