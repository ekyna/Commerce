<?php

namespace Ekyna\Component\Commerce\Tests\Common\Builder;

use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilder;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;

/**
 * Class AdjustmentBuilderTest
 * @package Ekyna\Component\Commerce\Tests\Common\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdjustmentBuilderTest extends TestCase
{
    /**
     * @var AdjustmentBuilderInterface
     */
    private $builder;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->builder = new AdjustmentBuilder(
            $this->getSaleFactory(),
            $this->getTaxResolverMock(),
            $this->getDiscountResolverMock(),
            $this->getPersistenceHelperMock()
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->builder = null;
    }

    /**
     * @covers AdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildItemDiscount_withSingleItem()
    {
        $item = Fixture::orderItem();
        $item->setOrder(Fixture::order());

        // Given the discount resolver will return a 7% discount adjustment data.
        $this
            ->getDiscountResolverMock()
            ->method('resolveSaleItem')
            ->with($item)
            ->willReturn([new AdjustmentData(AdjustmentModes::MODE_PERCENT, 'Discount 7%', 7, 'test')]);

        $this->builder->buildSaleItemDiscountAdjustments($item);

        $adjustments = $item->getAdjustments();
        $this->assertCount(1, $adjustments);

        $this->assertDiscount($adjustments[0], 7, 'Discount 7%', $item);
    }

    /**
     * @covers AdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildItemDiscount_withParentItemAndPublicChildren()
    {
        // TODO Assert discounts added to both parent and children items

        $this->markTestIncomplete();
    }

    /**
     * @covers AdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildItemDiscount_withParentItemAndPrivateChildren()
    {
        // TODO Assert discounts added only to parent item

        $this->markTestIncomplete();
    }

    /**
     * @covers AdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildSaleItemDiscount_withComposedItemAndPublicChildren()
    {
        // TODO Assert discounts added only to children items

        $this->markTestIncomplete();
    }

    /**
     * @covers AdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildSaleItemDiscount_withComposedItemAndPriveChildren()
    {
        // TODO Assert discounts added only to parent item

        $this->markTestIncomplete();
    }

    // TODO Sample sale case tests

    /**
     * Makes assertions on the given discount adjustment.
     *
     * @param AdjustmentInterface $discount
     * @param float               $amount
     * @param string              $designation
     * @param mixed               $adjustable
     */
    private function assertDiscount(AdjustmentInterface $discount, $amount, $designation, $adjustable)
    {
        $this->assertEquals(AdjustmentTypes::TYPE_DISCOUNT, $discount->getType());
        $this->assertEquals($amount, $discount->getAmount());
        $this->assertEquals($designation, $discount->getDesignation());
        $this->assertTrue($discount->isImmutable());
        $this->assertEquals($adjustable, $discount->getAdjustable());
    }
}
