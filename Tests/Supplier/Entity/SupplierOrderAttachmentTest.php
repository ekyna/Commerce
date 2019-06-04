<?php

namespace Ekyna\Component\Commerce\Tests\Supplier\Entity;

use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrder;
use Ekyna\Component\Commerce\Supplier\Entity\SupplierOrderAttachment;
use PHPUnit\Framework\TestCase;

/**
 * Class SupplierOrderAttachmentTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderAttachmentTest extends TestCase
{
    public function test_setOrder_withOrder(): void
    {
        $attachment = new SupplierOrderAttachment();
        $order = new SupplierOrder();

        $attachment->setSupplierOrder($order);

        $this->assertEquals($order, $attachment->getSupplierOrder());
        $this->assertTrue($order->hasAttachment($attachment));
    }

    public function test_setOrder_withNull(): void
    {
        $attachment = new SupplierOrderAttachment();
        $order = new SupplierOrder();

        $attachment->setSupplierOrder($order);
        $attachment->setSupplierOrder(null);

        $this->assertNull($attachment->getSupplierOrder());
        $this->assertFalse($order->hasAttachment($attachment));
    }

    public function test_setOrder_withAnotherOrder(): void
    {
        $attachment = new SupplierOrderAttachment();
        $orderA = new SupplierOrder();
        $orderB = new SupplierOrder();

        $attachment->setSupplierOrder($orderA);
        $attachment->setSupplierOrder($orderB);

        $this->assertEquals($orderB, $attachment->getSupplierOrder());
        $this->assertTrue($orderB->hasAttachment($attachment));
        $this->assertFalse($orderA->hasAttachment($attachment));
    }
}