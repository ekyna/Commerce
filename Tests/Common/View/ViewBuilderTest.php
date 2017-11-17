<?php

namespace Ekyna\Component\Commerce\Tests\Common\View;

use Ekyna\Component\Commerce\Common\View\ViewBuilder;

/**
 * Class BuilderTest
 * @package Ekyna\Component\Commerce\Tests\Common\View
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ViewBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ViewBuilder::buildSaleView()
     */
    public function test_buildPrivateSaleView()
    {
        // TODO All items shown

        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleView()
     */
    public function test_buildPublicSaleView()
    {
        // TODO Private items hidden

        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleItemLineView()
     */
    public function test_buildPrivateItemView_withSingleItem()
    {
        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleItemLineView()
     */
    public function test_buildPublicItemView_withSingleItem()
    {
        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleItemLineView()
     */
    public function test_buildPrivateItemView_withParentItem()
    {
        // TODO All items shown

        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleItemLineView()
     */
    public function test_buildPublicItemView_withParentItemAndPublicChildren()
    {
        // TODO All items shown

        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleItemLineView()
     */
    public function test_buildPublicItemView_withParentItemAndPrivateChildren()
    {
        // TODO Private children should be collapsed into parent

        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleItemLineView()
     */
    public function test_buildPrivateItemView_withComposedItem()
    {
        // TODO All items shown

        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleItemLineView()
     */
    public function test_buildPublicItemView_withComposedItemAndPublicChildren()
    {
        // TODO All items shown

        $this->markTestIncomplete();
    }

    /**
     * @covers ViewBuilder::buildSaleItemLineView()
     */
    public function test_buildPublicItemView_withComposedItemAndPrivateChildren()
    {
        // TODO Private children should be collapsed into parent

        $this->markTestIncomplete();
    }

    // TODO Test build discount/shipment line
}
