<?php

namespace Ekyna\Component\Commerce\Tests\Order\Entity;

use Ekyna\Component\Commerce\Order\Entity\OrderItem;
use Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment;
use Ekyna\Component\Commerce\Order\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Class OrderItemTest
 * @package Ekyna\Component\Commerce\Tests\Order\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @coversDefaultClass \Ekyna\Component\Commerce\Order\Entity\OrderItem
 */
class OrderItemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var OrderItemInterface
     */
    private $item;

    public function setUp()
    {
        $this->item = new OrderItem();
        $this->item
            ->setDesignation('Test order item 1')
            ->setReference('TEST-ITEM-1')
            ->setNetPrice(10)
            ->setTaxName('Tax 1')
            ->setTaxRate(0.2)
            ->setWeight(100)
            ->setQuantity(1)
            ->addChild($this->getTestChildItemOne())
            ->addAdjustment($this->getTestItemAdjustmentOne());
    }

    public function tearDown()
    {
        $this->item = null;
    }

    private function getTestChildItemOne()
    {
        $item = new OrderItem();
        $item
            ->setDesignation('Test child item 1')
            ->setReference('TEST-CHILD-1')
            ->setTaxName('Tax 1');
        return $item;
    }

    private function getTestChildItemTwo()
    {
        $item = new OrderItem();
        $item
            ->setDesignation('Test child item 2')
            ->setReference('TEST-CHILD-2')
            ->setTaxName('Tax 2');
        return $item;
    }

    private function getTestItemAdjustmentOne()
    {
        $adjustment = new OrderItemAdjustment();
        $adjustment
            ->setDesignation('Test item adjustment 1')
            ->setAmount(10);
        return $adjustment;
    }

    private function getTestItemAdjustmentTwo()
    {
        $adjustment = new OrderItemAdjustment();
        $adjustment
            ->setDesignation('Test item adjustment 2')
            ->setAmount(5)
            ->setMode(AdjustmentInterface::MODE_PERCENT);
        return $adjustment;
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
        $this->assertNull(
            $item->getNetPrice(),
            'OrderItem is not initialized with a null net price'
        );
        $this->assertNull(
            $item->getTaxName(),
            'OrderItem is not initialized with a null tax name'
        );
        $this->assertNull(
            $item->getTaxRate(),
            'OrderItem is not initialized with a null tax rate'
        );
        $this->assertNull(
            $item->getWeight(),
            'OrderItem is not initialized with a null weight'
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
