<?php

namespace Ekyna\Component\Commerce\Tests\Quote\Entity;

use Ekyna\Component\Commerce\Quote\Entity\Quote;
use Ekyna\Component\Commerce\Quote\Entity\QuotePayment;
use PHPUnit\Framework\TestCase;

/**
 * Class QuotePaymentTest
 * @package Ekyna\Component\Commerce\Tests\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuotePaymentTest extends TestCase
{
    public function test_setQuote_withQuote()
    {
        $payment = new QuotePayment();
        $quote = new Quote();

        $payment->setQuote($quote);

        $this->assertEquals($quote, $payment->getQuote());
        $this->assertTrue($quote->hasPayment($payment));
    }

    public function test_setQuote_withNull()
    {
        $payment = new QuotePayment();
        $quote = new Quote();

        $payment->setQuote($quote);
        $payment->setQuote(null);

        $this->assertEquals(null, $payment->getQuote());
        $this->assertFalse($quote->hasPayment($payment));
    }

    public function test_setQuote_withAnotherQuote()
    {
        $payment = new QuotePayment();
        $quoteA = new Quote();
        $quoteB = new Quote();

        $payment->setQuote($quoteA);
        $payment->setQuote($quoteB);

        $this->assertEquals($quoteB, $payment->getQuote());
        $this->assertTrue($quoteB->hasPayment($payment));
        $this->assertFalse($quoteA->hasPayment($payment));
    }
}