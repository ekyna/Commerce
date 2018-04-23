<?php

namespace Ekyna\Component\Commerce\Tests\Quote\Entity;

use Ekyna\Component\Commerce\Quote\Entity\Quote;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAddress;
use PHPUnit\Framework\TestCase;

/**
 * Class QuoteAddressTest
 * @package Ekyna\Component\Commerce\Tests\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAddressTest extends TestCase
{
    public function test_setInvoiceQuote_withQuote()
    {
        $address = new QuoteAddress();
        $quote = new Quote();

        $address->setInvoiceQuote($quote);

        $this->assertEquals($quote, $address->getInvoiceQuote());
        $this->assertEquals($address, $quote->getInvoiceAddress());
    }

    public function test_setInvoiceQuote_withNull()
    {
        $address = new QuoteAddress();
        $quote = new Quote();

        $address->setInvoiceQuote($quote);
        $address->setInvoiceQuote(null);

        $this->assertNull($address->getInvoiceQuote());
        $this->assertNull($quote->getInvoiceAddress());
    }

    public function test_setInvoiceQuote_withAnotherQuote()
    {
        $address = new QuoteAddress();
        $quoteA = new Quote();
        $quoteB = new Quote();

        $address->setInvoiceQuote($quoteA);
        $address->setInvoiceQuote($quoteB);

        $this->assertEquals($quoteB, $address->getInvoiceQuote());
        $this->assertEquals($address, $quoteB->getInvoiceAddress());
        $this->assertNull($quoteA->getInvoiceAddress());
    }
    
    public function test_setDeliveryQuote_withQuote()
    {
        $address = new QuoteAddress();
        $quote = new Quote();

        $address->setDeliveryQuote($quote);

        $this->assertEquals($quote, $address->getDeliveryQuote());
        $this->assertEquals($address, $quote->getDeliveryAddress());
    }

    public function test_setDeliveryQuote_withNull()
    {
        $address = new QuoteAddress();
        $quote = new Quote();

        $address->setDeliveryQuote($quote);
        $address->setDeliveryQuote(null);

        $this->assertNull($address->getDeliveryQuote());
        $this->assertNull($quote->getDeliveryAddress());
    }

    public function test_setDeliveryQuote_withAnotherQuote()
    {
        $address = new QuoteAddress();
        $quoteA = new Quote();
        $quoteB = new Quote();

        $address->setDeliveryQuote($quoteA);
        $address->setDeliveryQuote($quoteB);

        $this->assertEquals($quoteB, $address->getDeliveryQuote());
        $this->assertEquals($address, $quoteB->getDeliveryAddress());
        $this->assertNull($quoteA->getDeliveryAddress());
    }
}