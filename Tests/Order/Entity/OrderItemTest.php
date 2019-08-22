<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderItem;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderItemTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @coversDefaultClass \Ekyna\Component\Commerce\Order\Entity\OrderItem
 */
class OrderItemTest extends TestCase
{
    public function test_setOrder_withOrder()
    {
        $item = new OrderItem();
        $order = new Order();

        $item->setOrder($order);

        $this->assertEquals($order, $item->getOrder());
        $this->assertTrue($order->hasItem($item));
    }

    public function test_setOrder_withNull()
    {
        $item = new OrderItem();
        $order = new Order();

        $item->setOrder($order);
        $item->setOrder(null);

        $this->assertEquals(null, $item->getOrder());
        $this->assertFalse($order->hasItem($item));
    }

    public function test_setOrder_withAnotherOrder()
    {
        $item = new OrderItem();
        $orderA = new Order();
        $orderB = new Order();

        $item->setOrder($orderA);
        $item->setOrder($orderB);

        $this->assertEquals($orderB, $item->getOrder());
        $this->assertTrue($orderB->hasItem($item));
        $this->assertFalse($orderA->hasItem($item));
    }

    public function test_setParent_withItem()
    {
        $item = new OrderItem();
        $parent = new OrderItem();

        $item->setParent($parent);

        $this->assertEquals($parent, $item->getParent());
        $this->assertTrue($parent->hasChild($item));
    }

    public function test_setParent_withNull()
    {
        $item = new OrderItem();
        $parent = new OrderItem();

        $item->setParent($parent);
        $item->setParent(null);

        $this->assertEquals(null, $item->getParent());
        $this->assertFalse($parent->hasChild($item));
    }

    public function test_setParent_withAnotherItem()
    {
        $item = new OrderItem();
        $parentA = new OrderItem();
        $parentB = new OrderItem();

        $item->setParent($parentA);
        $item->setParent($parentB);

        $this->assertEquals($parentB, $item->getParent());
        $this->assertTrue($parentB->hasChild($item));
        $this->assertFalse($parentA->hasChild($item));
    }

    public function test_createChild()
    {
        $item = new OrderItem();

        $this->assertInstanceOf(OrderItem::class, $item->createChild());
    }

    /**
     * @covers ::__constructor
     */
    public function testInitialState()
    {
        $item = new OrderItem();

        $this->assertCount(
            0,
            $item->getChildren(),
            'OrderItem is not initialized with an empty child collection.'
        );
        $this->assertCount(
            0,
            $item->getAdjustments(),
            'OrderItem is not initialized with an empty adjustment collection.'
        );
        $this->assertEquals(
            0,
            $item->getNetPrice(),
            'OrderItem is not initialized with a zero net price'
        );
        $this->assertEquals(
            0,
            $item->getWeight(),
            'OrderItem is not initialized with a zero weight'
        );
        $this->assertNull(
            $item->getTaxGroup(),
            'OrderItem is not initialized with a null tax group'
        );
        $this->assertEquals(
            1,
            $item->getQuantity(),
            'OrderItem is not initialized with a quantity that equals 1'
        );
        $this->assertEquals(
            0,
            $item->getPosition(),
            'OrderItem is not initialized with a position that equals 0'
        );
    }
}
