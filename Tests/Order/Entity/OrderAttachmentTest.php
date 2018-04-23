<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderAttachment;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderAttachmentTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAttachmentTest extends TestCase
{
    public function test_setOrder_withOrder()
    {
        $attachment = new OrderAttachment();
        $order = new Order();

        $attachment->setOrder($order);

        $this->assertEquals($order, $attachment->getOrder());
        $this->assertTrue($order->hasAttachment($attachment));
    }

    public function test_setOrder_withNull()
    {
        $attachment = new OrderAttachment();
        $order = new Order();

        $attachment->setOrder($order);
        $attachment->setOrder(null);

        $this->assertEquals(null, $attachment->getOrder());
        $this->assertFalse($order->hasAttachment($attachment));
    }

    public function test_setOrder_withAnotherOrder()
    {
        $attachment = new OrderAttachment();
        $orderA = new Order();
        $orderB = new Order();

        $attachment->setOrder($orderA);
        $attachment->setOrder($orderB);

        $this->assertEquals($orderB, $attachment->getOrder());
        $this->assertTrue($orderB->hasAttachment($attachment));
        $this->assertFalse($orderA->hasAttachment($attachment));
    }
}