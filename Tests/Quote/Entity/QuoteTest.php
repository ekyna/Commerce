<?php

namespace Ekyna\Component\Commerce\Tests\Quote\Entity;

use Ekyna\Component\Commerce\Quote\Entity\Quote;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAddress;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAdjustment;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAttachment;
use Ekyna\Component\Commerce\Quote\Entity\QuoteItem;
use Ekyna\Component\Commerce\Quote\Entity\QuotePayment;
use PHPUnit\Framework\TestCase;

/**
 * Class QuoteTest
 * @package Ekyna\Component\Commerce\Tests\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteTest extends TestCase
{
    public function test_setInvoiceAddress_withAddress()
    {
        $quote = new Quote();
        $address = new QuoteAddress();

        $quote->setInvoiceAddress($address);

        $this->assertEquals($address, $quote->getInvoiceAddress());
        $this->assertEquals($quote, $address->getInvoiceQuote());
    }

    public function test_setInvoiceAddress_withNull()
    {
        $quote = new Quote();
        $address = new QuoteAddress();

        $quote->setInvoiceAddress($address);
        $quote->setInvoiceAddress(null);

        $this->assertNull($quote->getInvoiceAddress());
        $this->assertNull($address->getInvoiceQuote());
    }

    public function test_setInvoiceAddress_withAnotherAddress()
    {
        $quote = new Quote();
        $addressA = new QuoteAddress();
        $addressB = new QuoteAddress();

        $quote->setInvoiceAddress($addressA);
        $quote->setInvoiceAddress($addressB);

        $this->assertEquals($addressB, $quote->getInvoiceAddress());
        $this->assertEquals($quote, $addressB->getInvoiceQuote());
        $this->assertNull($addressA->getInvoiceQuote());
    }
    
    public function test_setDeliveryAddress_withAddress()
    {
        $quote = new Quote();
        $address = new QuoteAddress();

        $quote->setDeliveryAddress($address);

        $this->assertEquals($address, $quote->getDeliveryAddress());
        $this->assertEquals($quote, $address->getDeliveryQuote());
    }

    public function test_setDeliveryAddress_withNull()
    {
        $quote = new Quote();
        $address = new QuoteAddress();

        $quote->setDeliveryAddress($address);
        $quote->setDeliveryAddress(null);

        $this->assertNull($quote->getDeliveryAddress());
        $this->assertNull($address->getDeliveryQuote());
    }

    public function test_setDeliveryAddress_withAnotherAddress()
    {
        $quote = new Quote();
        $addressA = new QuoteAddress();
        $addressB = new QuoteAddress();

        $quote->setDeliveryAddress($addressA);
        $quote->setDeliveryAddress($addressB);

        $this->assertEquals($addressB, $quote->getDeliveryAddress());
        $this->assertEquals($quote, $addressB->getDeliveryQuote());
        $this->assertNull($addressA->getDeliveryQuote());
    }

    public function test_addAttachment()
    {
        $quote = new Quote();
        $attachment = new QuoteAttachment();

        $quote->addAttachment($attachment);

        $this->assertEquals($quote, $attachment->getQuote());
        $this->assertTrue($quote->hasAttachment($attachment));
    }

    public function test_removeAttachment()
    {
        $quote = new Quote();
        $attachment = new QuoteAttachment();

        $quote->addAttachment($attachment);
        $quote->removeAttachment($attachment);

        $this->assertNull($attachment->getQuote());
        $this->assertFalse($quote->hasAttachment($attachment));
    }
    
    public function test_addAdjustment()
    {
        $quote = new Quote();
        $adjustment = new QuoteAdjustment();

        $quote->addAdjustment($adjustment);

        $this->assertEquals($quote, $adjustment->getQuote());
        $this->assertTrue($quote->hasAdjustment($adjustment));
    }

    public function test_removeAdjustment()
    {
        $quote = new Quote();
        $adjustment = new QuoteAdjustment();

        $quote->addAdjustment($adjustment);
        $quote->removeAdjustment($adjustment);

        $this->assertNull($adjustment->getQuote());
        $this->assertFalse($quote->hasAdjustment($adjustment));
    }
    
    public function test_addItem()
    {
        $quote = new Quote();
        $item = new QuoteItem();

        $quote->addItem($item);

        $this->assertEquals($quote, $item->getQuote());
        $this->assertTrue($quote->hasItem($item));
    }

    public function test_removeItem()
    {
        $quote = new Quote();
        $item = new QuoteItem();

        $quote->addItem($item);
        $quote->removeItem($item);

        $this->assertNull($item->getQuote());
        $this->assertFalse($quote->hasItem($item));
    }
    
    public function test_addPayment()
    {
        $quote = new Quote();
        $payment = new QuotePayment();

        $quote->addPayment($payment);

        $this->assertEquals($quote, $payment->getQuote());
        $this->assertTrue($quote->hasPayment($payment));
    }

    public function test_removePayment()
    {
        $quote = new Quote();
        $payment = new QuotePayment();

        $quote->addPayment($payment);
        $quote->removePayment($payment);

        $this->assertNull($payment->getQuote());
        $this->assertFalse($quote->hasPayment($payment));
    }
}