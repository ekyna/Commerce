<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderAdjustment;
use Ekyna\Component\Commerce\Order\Entity\OrderAttachment;
use Ekyna\Component\Commerce\Order\Entity\OrderItem;
use Ekyna\Component\Commerce\Pricing\Entity\TaxGroup;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderItem
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @coversDefaultClass \Ekyna\Component\Commerce\Order\Entity\Order
 */
class OrderTest extends TestCase
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var TaxGroup
     */
    private $taxGroup;

    public function setUp(): void
    {
        $this->taxGroup = new TaxGroup();

        $this->order = new Order();
        $this->order
            ->addItem($this->getTestItemOne())
            ->addAdjustment($this->getTestAdjustmentOne())
            ->addAttachment($this->getTestAttachmentOne());
    }

    public function tearDown(): void
    {
        $this->order = null;
        $this->taxGroup = null;
    }

    private function getTestItemOne()
    {
        $item = new OrderItem();
        $item
            ->setDesignation('Test order item 1')
            ->setReference('TEST-ITEM-1')
            ->setTaxGroup($this->taxGroup);

        return $item;
    }

    private function getTestItemTwo()
    {
        $item = new OrderItem();
        $item
            ->setDesignation('Test order item 2')
            ->setReference('TEST-ITEM-2')
            ->setTaxGroup($this->taxGroup);

        return $item;
    }

    private function getTestAdjustmentOne()
    {
        $adjustment = new OrderAdjustment();
        $adjustment
            ->setDesignation('Test adjustment 1')
            ->setAmount(10);

        return $adjustment;
    }

    private function getTestAdjustmentTwo()
    {
        $adjustment = new OrderAdjustment();
        $adjustment
            ->setDesignation('Test adjustment 2')
            ->setAmount(5)
            ->setMode(AdjustmentModes::MODE_PERCENT);

        return $adjustment;
    }

    private function getTestAttachmentOne()
    {
        $attachment = new OrderAttachment();
        $attachment->setTitle('Test attachment 1');

        return $attachment;
    }

    private function getTestAttachmentTwo()
    {
        $attachment = new OrderAttachment();
        $attachment->setTitle('Test attachment 2');

        return $attachment;
    }

    /**
     * @covers ::__constructor
     */
    public function testInitialState()
    {
        $order = new Order();

        $this->assertCount(
            0,
            $order->getItems(),
            'Order::__constructor() does not create an empty item collection.'
        );
        $this->assertCount(
            0,
            $order->getAdjustments(),
            'Order::__constructor() does not create an empty adjustment collection.'
        );
    }

    /**
     * @covers ::hasItem
     */
    public function testHasItem()
    {
        $item = $this->order->getItems()->last();

        $this->assertTrue(
            $this->order->hasItem($item),
            'Order::hasItem() does not return true with the same item.'
        );
        $this->assertFalse(
            $this->order->hasItem($this->getTestItemTwo()),
            'Order::hasItem() does not return false with a different item.'
        );
    }

    /**
     * @covers ::addItem
     */
    public function testAddItem()
    {
        $item = $this->getTestItemTwo();

        $return = $this->order->addItem($item);

        $this->assertEquals(
            $this->order->getItems()->count(),
            2,
            'Order::addItem() does not add the item.'
        );
        $this->assertEquals(
            $item->getOrder(),
            $this->order,
            'Order::addItem() does not set the order reference.'
        );
        $this->assertEquals(
            $return,
            $this->order,
            'Order::addItem() is not fluent.'
        );
    }

    /**
     * @covers ::removeItem
     */
    public function testRemoveItem()
    {
        $item = $this->order->getItems()->last();

        $return = $this->order->removeItem($item);

        $this->assertEquals(
            $this->order->getItems()->count(),
            0,
            'Order::removeItem() does not remove the item.'
        );
        $this->assertNull(
            $item->getOrder(),
            'Order::removeItem() does not remove the order reference.'
        );
        $this->assertEquals(
            $return,
            $this->order,
            'Order::removeItem() is not fluent.'
        );
    }

    /**
     * @covers ::hasAdjustment
     */
    public function testHasAdjustment()
    {
        $adjustment = $this->order->getAdjustments()->last();

        $this->assertTrue(
            $this->order->hasAdjustment($adjustment),
            'Order::hasAdjustment() does not return true with the same adjustment.'
        );
        $this->assertFalse(
            $this->order->hasAdjustment($this->getTestAdjustmentTwo()),
            'Order::hasAdjustment() does not return false with a different adjustment.'
        );
    }

    /**
     * @covers ::addAdjustment
     */
    public function testAddAdjustment()
    {
        $adjustment = $this->getTestAdjustmentTwo();

        $return = $this->order->addAdjustment($adjustment);

        $this->assertEquals(
            $this->order->getAdjustments()->count(),
            2,
            'Order::addAdjustment() does not add the adjustment.'
        );
        $this->assertEquals(
            $adjustment->getOrder(),
            $this->order,
            'Order::addAdjustment() does not set the order reference.'
        );
        $this->assertEquals(
            $return,
            $this->order,
            'Order::addAdjustment() is not fluent.'
        );
    }

    /**
     * @covers ::removeAdjustment
     */
    public function testRemoveAdjustment()
    {
        $adjustment = $this->order->getAdjustments()->last();

        $return = $this->order->removeAdjustment($adjustment);

        $this->assertEquals(
            $this->order->getAdjustments()->count(),
            0,
            'Order::removeAdjustment() does not remove the adjustment.'
        );
        $this->assertNull(
            $adjustment->getOrder(),
            'Order::removeAdjustment() does not remove the order reference.'
        );
        $this->assertEquals(
            $return,
            $this->order,
            'Order::removeAdjustment() is not fluent.'
        );
    }

    /**
     * @covers ::hasAttachment
     */
    public function testHasAttachment()
    {
        $attachment = $this->order->getAttachments()->last();

        $this->assertTrue(
            $this->order->hasAttachment($attachment),
            'Order::hasAttachment() does not return true with the same attachment.'
        );
        $this->assertFalse(
            $this->order->hasAttachment($this->getTestAttachmentTwo()),
            'Order::hasAttachment() does not return false with a different attachment.'
        );
    }

    /**
     * @covers ::addAttachment
     */
    public function testAddAttachment()
    {
        $attachment = $this->getTestAttachmentTwo();

        $return = $this->order->addAttachment($attachment);

        $this->assertEquals(
            $this->order->getAttachments()->count(),
            2,
            'Order::addAttachment() does not add the attachment.'
        );
        $this->assertEquals(
            $attachment->getOrder(),
            $this->order,
            'Order::addAttachment() does not set the order reference.'
        );
        $this->assertEquals(
            $return,
            $this->order,
            'Order::addAttachment() is not fluent.'
        );
    }

    /**
     * @covers ::removeAttachment
     */
    public function testRemoveAttachment()
    {
        $attachment = $this->order->getAttachments()->last();

        $return = $this->order->removeAttachment($attachment);

        $this->assertEquals(
            $this->order->getAttachments()->count(),
            0,
            'Order::removeAttachment() does not remove the attachment.'
        );
        $this->assertNull(
            $attachment->getOrder(),
            'Order::removeAttachment() does not remove the order reference.'
        );
        $this->assertEquals(
            $return,
            $this->order,
            'Order::removeAttachment() is not fluent.'
        );
    }
}
