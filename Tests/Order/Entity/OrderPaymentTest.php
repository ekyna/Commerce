<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\Order;
use Ekyna\Component\Commerce\Order\Entity\OrderPayment;
use PHPUnit\Framework\TestCase;

/**
 * Class OrderPaymentTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentTest extends TestCase
{
    public function test_setOrder_withOrder()
    {
        $payment = new OrderPayment();
        $order = new Order();

        $payment->setOrder($order);

        $this->assertEquals($order, $payment->getOrder());
        $this->assertTrue($order->hasPayment($payment));
    }

    public function test_setOrder_withNull()
    {
        $payment = new OrderPayment();
        $order = new Order();

        $payment->setOrder($order);
        $payment->setOrder(null);

        $this->assertNull($payment->getOrder());
        $this->assertFalse($order->hasPayment($payment));
    }

    public function test_setOrder_withAnotherOrder()
    {
        $payment = new OrderPayment();
        $orderA = new Order();
        $orderB = new Order();

        $payment->setOrder($orderA);
        $payment->setOrder($orderB);

        $this->assertEquals($orderB, $payment->getOrder());
        $this->assertTrue($orderB->hasPayment($payment));
        $this->assertFalse($orderA->hasPayment($payment));
    }
}