<?php

namespace Ekyna\Component\Commerce\Tests\Quote\Entity;

use Ekyna\Component\Commerce\Quote\Entity\Quote;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAdjustment;
use PHPUnit\Framework\TestCase;

/**
 * Class QuoteAdjustmentTest
 * @package Ekyna\Component\Commerce\Tests\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAdjustmentTest extends TestCase
{
    public function test_setQuote_withQuote()
    {
        $adjustment = new QuoteAdjustment();
        $quote = new Quote();

        $adjustment->setQuote($quote);

        $this->assertEquals($quote, $adjustment->getQuote());
        $this->assertTrue($quote->hasAdjustment($adjustment));
    }

    public function test_setQuote_withNull()
    {
        $adjustment = new QuoteAdjustment();
        $quote = new Quote();

        $adjustment->setQuote($quote);
        $adjustment->setQuote(null);

        $this->assertEquals(null, $adjustment->getQuote());
        $this->assertFalse($quote->hasAdjustment($adjustment));
    }

    public function test_setQuote_withAnotherQuote()
    {
        $adjustment = new QuoteAdjustment();
        $quoteA = new Quote();
        $quoteB = new Quote();

        $adjustment->setQuote($quoteA);
        $adjustment->setQuote($quoteB);

        $this->assertEquals($quoteB, $adjustment->getQuote());
        $this->assertTrue($quoteB->hasAdjustment($adjustment));
        $this->assertFalse($quoteA->hasAdjustment($adjustment));
    }
}