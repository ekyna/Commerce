<?php

namespace Ekyna\Component\Commerce\Tests\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Entity\Cart;
use Ekyna\Component\Commerce\Cart\Entity\CartAddress;
use PHPUnit\Framework\TestCase;

/**
 * Class CartAddressTest
 * @package Ekyna\Component\Commerce\Tests\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddressTest extends TestCase
{
    public function test_setInvoiceCart_withCart()
    {
        $address = new CartAddress();
        $cart = new Cart();

        $address->setInvoiceCart($cart);

        $this->assertEquals($cart, $address->getInvoiceCart());
        $this->assertEquals($address, $cart->getInvoiceAddress());
    }

    public function test_setInvoiceCart_withNull()
    {
        $address = new CartAddress();
        $cart = new Cart();

        $address->setInvoiceCart($cart);
        $address->setInvoiceCart(null);

        $this->assertNull($address->getInvoiceCart());
        $this->assertNull($cart->getInvoiceAddress());
    }

    public function test_setInvoiceCart_withAnotherCart()
    {
        $address = new CartAddress();
        $cartA = new Cart();
        $cartB = new Cart();

        $address->setInvoiceCart($cartA);
        $address->setInvoiceCart($cartB);

        $this->assertEquals($cartB, $address->getInvoiceCart());
        $this->assertEquals($address, $cartB->getInvoiceAddress());
        $this->assertNull($cartA->getInvoiceAddress());
    }
    
    public function test_setDeliveryCart_withCart()
    {
        $address = new CartAddress();
        $cart = new Cart();

        $address->setDeliveryCart($cart);

        $this->assertEquals($cart, $address->getDeliveryCart());
        $this->assertEquals($address, $cart->getDeliveryAddress());
    }

    public function test_setDeliveryCart_withNull()
    {
        $address = new CartAddress();
        $cart = new Cart();

        $address->setDeliveryCart($cart);
        $address->setDeliveryCart(null);

        $this->assertNull($address->getDeliveryCart());
        $this->assertNull($cart->getDeliveryAddress());
    }

    public function test_setDeliveryCart_withAnotherCart()
    {
        $address = new CartAddress();
        $cartA = new Cart();
        $cartB = new Cart();

        $address->setDeliveryCart($cartA);
        $address->setDeliveryCart($cartB);

        $this->assertEquals($cartB, $address->getDeliveryCart());
        $this->assertEquals($address, $cartB->getDeliveryAddress());
        $this->assertNull($cartA->getDeliveryAddress());
    }
}