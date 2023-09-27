<?php
/** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Builder;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilder;
use Ekyna\Component\Commerce\Common\Builder\AdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Builder\SaleAdjustmentBuilder;
use Ekyna\Component\Commerce\Common\Builder\SaleAdjustmentBuilderInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentData;
use Ekyna\Component\Commerce\Common\Model\AdjustmentInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentModes;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Order\Entity\OrderItemAdjustment;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;

/**
 * Class AdjustmentBuilderTest
 * @package Ekyna\Component\Commerce\Tests\Common\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SaleAdjustmentBuilderTest extends TestCase
{
    private ?AdjustmentBuilderInterface     $adjustmentBuilder     = null;
    private ?SaleAdjustmentBuilderInterface $saleAdjustmentBuilder = null;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->adjustmentBuilder = new AdjustmentBuilder(
            $this->getFactoryHelperMock(),
            $this->getPersistenceHelperMock(),
        );

        $this->saleAdjustmentBuilder = new SaleAdjustmentBuilder(
            $this->adjustmentBuilder,
            $this->getTaxResolverMock(),
            $this->getDiscountResolverMock(),
        );
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->saleAdjustmentBuilder = null;
        $this->adjustmentBuilder = null;
    }

    /**
     * @covers SaleAdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildItemDiscount_withSingleItem()
    {
        $item = Fixture::orderItem();
        $item->setOrder(Fixture::order());

        // Given the discount resolver will return a 7% discount adjustment data.
        $this
            ->getFactoryHelperMock()
            ->method('createAdjustmentFor')
            ->with($item)
            ->willReturn(new OrderItemAdjustment());

        // Given the discount resolver will return a 7% discount adjustment data.
        $this
            ->getDiscountResolverMock()
            ->method('resolveSaleItem')
            ->with($item)
            ->willReturn([
                new AdjustmentData(AdjustmentModes::MODE_PERCENT, 'Discount 7%', new Decimal(7), 'test'),
            ]);

        $this->saleAdjustmentBuilder->buildSaleItemDiscountAdjustments($item);

        $adjustments = $item->getAdjustments();
        self::assertCount(1, $adjustments);

        $this->assertDiscount($adjustments[0], 7, 'Discount 7%', $item);
    }

    /**
     * @covers SaleAdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildItemDiscount_withParentItemAndPublicChildren()
    {
        // TODO Assert discounts added to both parent and children items

        $this->markTestIncomplete();
    }

    /**
     * @covers SaleAdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildItemDiscount_withParentItemAndPrivateChildren()
    {
        // TODO Assert discounts added only to parent item

        $this->markTestIncomplete();
    }

    /**
     * @covers SaleAdjustmentBuilder::buildSaleItemDiscountAdjustments()
     */
    public function test_buildSaleItemDiscount_withComposedItemAndPublicChildren()
    {
        // TODO Assert discounts added only to children items

        $this->markTestIncomplete();
    }

    /**
     * @covers SaleAdjustmentBuilder::buildSaleItemDiscountAdjustments()
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
    private function assertDiscount(AdjustmentInterface $discount, $amount, $designation, $adjustable): void
    {
        self::assertEquals(AdjustmentTypes::TYPE_DISCOUNT, $discount->getType());
        self::assertEquals(new Decimal((string)$amount), $discount->getAmount());
        self::assertEquals($designation, $discount->getDesignation());
        self::assertTrue($discount->isImmutable());
        self::assertEquals($adjustable, $discount->getAdjustable());
    }
}
