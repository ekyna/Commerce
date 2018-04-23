<?php

namespace Ekyna\Component\Commerce\Tests\Quote\Entity;

use Ekyna\Component\Commerce\Quote\Entity\QuoteItem;
use Ekyna\Component\Commerce\Quote\Entity\QuoteItemAdjustment;
use PHPUnit\Framework\TestCase;

/**
 * Class QuoteItemAdjustmentTest
 * @package Ekyna\Component\Commerce\Tests\Quote\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemAdjustmentTest extends TestCase
{
    public function test_setItem_withItem()
    {
        $adjustment = new QuoteItemAdjustment();
        $item = new QuoteItem();

        $adjustment->setItem($item);

        $this->assertEquals($item, $adjustment->getItem());
        $this->assertTrue($item->hasAdjustment($adjustment));
    }

    public function test_setItem_withNull()
    {
        $adjustment = new QuoteItemAdjustment();
        $item = new QuoteItem();

        $adjustment->setItem($item);
        $adjustment->setItem(null);

        $this->assertEquals(null, $adjustment->getItem());
        $this->assertFalse($item->hasAdjustment($adjustment));
    }

    public function test_setItem_withAnotherItem()
    {
        $adjustment = new QuoteItemAdjustment();
        $itemA = new QuoteItem();
        $itemB = new QuoteItem();

        $adjustment->setItem($itemA);
        $adjustment->setItem($itemB);

        $this->assertEquals($itemB, $adjustment->getItem());
        $this->assertTrue($itemB->hasAdjustment($adjustment));
        $this->assertFalse($itemA->hasAdjustment($adjustment));
    }
}