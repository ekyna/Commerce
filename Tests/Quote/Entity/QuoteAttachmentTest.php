<?php

namespace Ekyna\Component\Commerce\Tests\Quote\Entity;

use Ekyna\Component\Commerce\Quote\Entity\Quote;
use Ekyna\Component\Commerce\Quote\Entity\QuoteAttachment;
use PHPUnit\Framework\TestCase;

/**
 * Class QuoteAttachmentTest
 * @package Ekyna\Component\Commerce\Tests\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAttachmentTest extends TestCase
{
    public function test_setQuote_withQuote()
    {
        $attachment = new QuoteAttachment();
        $quote = new Quote();

        $attachment->setQuote($quote);

        $this->assertEquals($quote, $attachment->getQuote());
        $this->assertTrue($quote->hasAttachment($attachment));
    }

    public function test_setQuote_withNull()
    {
        $attachment = new QuoteAttachment();
        $quote = new Quote();

        $attachment->setQuote($quote);
        $attachment->setQuote(null);

        $this->assertEquals(null, $attachment->getQuote());
        $this->assertFalse($quote->hasAttachment($attachment));
    }

    public function test_setQuote_withAnotherQuote()
    {
        $attachment = new QuoteAttachment();
        $quoteA = new Quote();
        $quoteB = new Quote();

        $attachment->setQuote($quoteA);
        $attachment->setQuote($quoteB);

        $this->assertEquals($quoteB, $attachment->getQuote());
        $this->assertTrue($quoteB->hasAttachment($attachment));
        $this->assertFalse($quoteA->hasAttachment($attachment));
    }
}