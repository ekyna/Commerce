<?php

namespace Ekyna\Component\Commerce\Tests\Quote\Entity;

use Ekyna\Component\Commerce\Quote\Entity\Quote;
use Ekyna\Component\Commerce\Quote\Entity\QuoteItem;
use PHPUnit\Framework\TestCase;

/**
 * Class QuoteItemTest
 * @package Ekyna\Component\Commerce\Tests\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemTest extends TestCase
{
    public function test_setQuote_withQuote()
    {
        $item = new QuoteItem();
        $quote = new Quote();

        $item->setQuote($quote);

        $this->assertEquals($quote, $item->getQuote());
        $this->assertTrue($quote->hasItem($item));
    }

    public function test_setQuote_withNull()
    {
        $item = new QuoteItem();
        $quote = new Quote();

        $item->setQuote($quote);
        $item->setQuote(null);

        $this->assertEquals(null, $item->getQuote());
        $this->assertFalse($quote->hasItem($item));
    }

    public function test_setQuote_withAnotherQuote()
    {
        $item = new QuoteItem();
        $quoteA = new Quote();
        $quoteB = new Quote();

        $item->setQuote($quoteA);
        $item->setQuote($quoteB);

        $this->assertEquals($quoteB, $item->getQuote());
        $this->assertTrue($quoteB->hasItem($item));
        $this->assertFalse($quoteA->hasItem($item));
    }

    public function test_setParent_withItem()
    {
        $item = new QuoteItem();
        $parent = new QuoteItem();

        $item->setParent($parent);

        $this->assertEquals($parent, $item->getParent());
        $this->assertTrue($parent->hasChild($item));
    }

    public function test_setParent_withNull()
    {
        $item = new QuoteItem();
        $parent = new QuoteItem();

        $item->setParent($parent);
        $item->setParent(null);

        $this->assertEquals(null, $item->getParent());
        $this->assertFalse($parent->hasChild($item));
    }

    public function test_setParent_withAnotherItem()
    {
        $item = new QuoteItem();
        $parentA = new QuoteItem();
        $parentB = new QuoteItem();

        $item->setParent($parentA);
        $item->setParent($parentB);

        $this->assertEquals($parentB, $item->getParent());
        $this->assertTrue($parentB->hasChild($item));
        $this->assertFalse($parentA->hasChild($item));
    }

    public function test_createChild()
    {
        $item = new QuoteItem();

        $this->assertInstanceOf(QuoteItem::class, $item->createChild());
    }
}