<?php

namespace Ekyna\Component\Commerce\Tests\Cart\Entity;

use Ekyna\Component\Commerce\Cart\Entity\Cart;
use Ekyna\Component\Commerce\Cart\Entity\CartAddress;
use Ekyna\Component\Commerce\Cart\Entity\CartAdjustment;
use Ekyna\Component\Commerce\Cart\Entity\CartAttachment;
use Ekyna\Component\Commerce\Cart\Entity\CartItem;
use Ekyna\Component\Commerce\Cart\Entity\CartPayment;
use PHPUnit\Framework\TestCase;

/**
 * Class CartTest
 * @package Ekyna\Component\Commerce\Tests\Cart\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartTest extends TestCase
{
    public function test_setInvoiceAddress_withAddress()
    {
        $cart = new Cart();
        $address = new CartAddress();

        $cart->setInvoiceAddress($address);

        $this->assertEquals($address, $cart->getInvoiceAddress());
        $this->assertEquals($cart, $address->getInvoiceCart());
    }

    public function test_setInvoiceAddress_withNull()
    {
        $cart = new Cart();
        $address = new CartAddress();

        $cart->setInvoiceAddress($address);
        $cart->setInvoiceAddress(null);

        $this->assertNull($cart->getInvoiceAddress());
        $this->assertNull($address->getInvoiceCart());
    }

    public function test_setInvoiceAddress_withAnotherAddress()
    {
        $cart = new Cart();
        $addressA = new CartAddress();
        $addressB = new CartAddress();

        $cart->setInvoiceAddress($addressA);
        $cart->setInvoiceAddress($addressB);

        $this->assertEquals($addressB, $cart->getInvoiceAddress());
        $this->assertEquals($cart, $addressB->getInvoiceCart());
        $this->assertNull($addressA->getInvoiceCart());
    }
    
    public function test_setDeliveryAddress_withAddress()
    {
        $cart = new Cart();
        $address = new CartAddress();

        $cart->setDeliveryAddress($address);

        $this->assertEquals($address, $cart->getDeliveryAddress());
        $this->assertEquals($cart, $address->getDeliveryCart());
    }

    public function test_setDeliveryAddress_withNull()
    {
        $cart = new Cart();
        $address = new CartAddress();

        $cart->setDeliveryAddress($address);
        $cart->setDeliveryAddress(null);

        $this->assertNull($cart->getDeliveryAddress());
        $this->assertNull($address->getDeliveryCart());
    }

    public function test_setDeliveryAddress_withAnotherAddress()
    {
        $cart = new Cart();
        $addressA = new CartAddress();
        $addressB = new CartAddress();

        $cart->setDeliveryAddress($addressA);
        $cart->setDeliveryAddress($addressB);

        $this->assertEquals($addressB, $cart->getDeliveryAddress());
        $this->assertEquals($cart, $addressB->getDeliveryCart());
        $this->assertNull($addressA->getDeliveryCart());
    }

    public function test_addAttachment()
    {
        $cart = new Cart();
        $attachment = new CartAttachment();

        $cart->addAttachment($attachment);

        $this->assertEquals($cart, $attachment->getCart());
        $this->assertTrue($cart->hasAttachment($attachment));
    }

    public function test_removeAttachment()
    {
        $cart = new Cart();
        $attachment = new CartAttachment();

        $cart->addAttachment($attachment);
        $cart->removeAttachment($attachment);

        $this->assertNull($attachment->getCart());
        $this->assertFalse($cart->hasAttachment($attachment));
    }
    
    public function test_addAdjustment()
    {
        $cart = new Cart();
        $adjustment = new CartAdjustment();

        $cart->addAdjustment($adjustment);

        $this->assertEquals($cart, $adjustment->getCart());
        $this->assertTrue($cart->hasAdjustment($adjustment));
    }

    public function test_removeAdjustment()
    {
        $cart = new Cart();
        $adjustment = new CartAdjustment();

        $cart->addAdjustment($adjustment);
        $cart->removeAdjustment($adjustment);

        $this->assertNull($adjustment->getCart());
        $this->assertFalse($cart->hasAdjustment($adjustment));
    }
    
    public function test_addItem()
    {
        $cart = new Cart();
        $item = new CartItem();

        $cart->addItem($item);

        $this->assertEquals($cart, $item->getCart());
        $this->assertTrue($cart->hasItem($item));
    }

    public function test_removeItem()
    {
        $cart = new Cart();
        $item = new CartItem();

        $cart->addItem($item);
        $cart->removeItem($item);

        $this->assertNull($item->getCart());
        $this->assertFalse($cart->hasItem($item));
    }
    
    public function test_addPayment()
    {
        $cart = new Cart();
        $payment = new CartPayment();

        $cart->addPayment($payment);

        $this->assertEquals($cart, $payment->getCart());
        $this->assertTrue($cart->hasPayment($payment));
    }

    public function test_removePayment()
    {
        $cart = new Cart();
        $payment = new CartPayment();

        $cart->addPayment($payment);
        $cart->removePayment($payment);

        $this->assertNull($payment->getCart());
        $this->assertFalse($cart->hasPayment($payment));
    }
}