<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculator;
use Ekyna\Component\Commerce\Common\Context\ContextProviderInterface;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Tests\Common\Model\AbstractAmountTest;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AmountCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AmountCalculatorTest extends AbstractAmountTest
{
    private const C = 'EUR';

    /**
     * @var ContextProviderInterface|MockObject
     */
    private $context;

    /**
     * @var AmountCalculator
     */
    private $calculator;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextProviderInterface::class);

        $this->calculator = new AmountCalculator($this->context, $this->getCurrencyConverter());
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->calculator = null;
    }

    /**
     * @covers AmountCalculator::calculateSale()
     */
    public function test_calculateSale()
    {
        $sale = Fixtures::createOrder(self::C);

        $item1 = Fixtures::createOrderItem(3, 32.59, [7], [20]);

        $sale->addItem($item1);

        $item2 = Fixtures::createOrderItem(4, 0);

        $item21 = Fixtures::createOrderItem(5, 12.34, [5], [5.5]);
        $item2->addChild($item21);

        $item22 = Fixtures::createOrderItem(2, 47.99, [10], [5.5]);
        $item2->addChild($item22);

        $item221 = Fixtures::createOrderItem(2, 3.99)->setPrivate(true);
        $item22->addChild($item221);

        $item222 = Fixtures::createOrderItem(3, 4.99)->setPrivate(true);
        $item22->addChild($item222);

        $sale->addItem($item2);

        $item3 = Fixtures::createOrderItem(6, 11.36, [15], [7]);
        $sale->addItem($item3);

        $item31 = Fixtures::createOrderItem(2, 4.14)->setPrivate(true);
        $item3->addChild($item31);

        $sale->addItem($item3);

        $sale->addAdjustment(Fixtures::createOrderDiscountAdjustment(12));
        $sale->addAdjustment(Fixtures::createOrderTaxationAdjustment(20));
        $sale->setShipmentAmount(15.26);

        $this->calculator->calculateSale($sale);

        $this->assertResult($item1->getResult(self::C), 32.59, 97.77, 6.84, 90.93, 18.19, 109.12);
        $this->assertResult($item2->getResult(self::C), 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);
        $this->assertResult($item21->getResult(self::C), 12.34, 246.80, 12.34, 234.46, 12.90, 247.36);
        $this->assertResult($item22->getResult(self::C), 70.94, 567.52, 56.75, 510.77, 28.09, 538.86);
        $this->assertResult($item3->getResult(self::C), 19.64, 117.84, 17.68, 100.16, 7.01, 107.17);

        $grossResult = $sale->getGrossResult(self::C);

        $taxes = $grossResult->getTaxAdjustments();
        $this->assertCount(3, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 18.19, 20);
        $this->assertAdjustment($taxes[1], 'VAT 5.5%', 40.99, 5.5);
        $this->assertAdjustment($taxes[2], 'VAT 7%', 7.01, 7);

        $discounts = $grossResult->getDiscountAdjustments();
        $this->assertCount(4, $discounts);
        $this->assertAdjustment($discounts[0], 'Discount 7%', 6.84, 7);
        $this->assertAdjustment($discounts[1], 'Discount 5%', 12.34, 5);
        $this->assertAdjustment($discounts[2], 'Discount 10%', 56.75, 10);
        $this->assertAdjustment($discounts[3], 'Discount 15%', 17.68, 15);

        $discountResult = $sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT)[0]->getResult(self::C);
        $shipmentResult = $sale->getShipmentResult(self::C);
        $finalResult    = $sale->getFinalResult(self::C);

        $this->assertResult($grossResult, 1029.93, 1029.93, 93.61, 936.32, 66.19, 1002.51);

        $this->assertResult($discountResult, 112.36, 112.36, 0.00, 112.36, 7.94, 120.30);

        $this->assertResult($shipmentResult, 15.26, 15.26, 0.00, 15.26, 3.05, 18.31);

        $this->assertResult($finalResult, 936.32, 936.32, 112.36, 839.22, 61.30, 900.52);

        $taxes = $finalResult->getTaxAdjustments();
        $this->assertCount(3, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 5.5%', 36.07, 5.5);
        $this->assertAdjustment($taxes[1], 'VAT 7%', 6.17, 7);
        $this->assertAdjustment($taxes[2], 'VAT 20%', 19.06, 20);
    }

    /**
     * @covers AmountCalculator::calculateSale()
     */
    public function test_calculateSale_withoutCache()
    {
        $sale = Fixtures::createOrder(self::C);

        $item1 = Fixtures::createOrderItem(3, 32.59, [7], [20]);

        $sale->addItem($item1);

        $item2 = Fixtures::createOrderItem(4, 0);

        $item21 = Fixtures::createOrderItem(5, 12.34, [5], [5.5]);
        $item2->addChild($item21);

        $item22 = Fixtures::createOrderItem(2, 47.99, [10], [5.5]);
        $item2->addChild($item22);

        $item221 = Fixtures::createOrderItem(2, 3.99)->setPrivate(true);
        $item22->addChild($item221);

        $item222 = Fixtures::createOrderItem(3, 4.99)->setPrivate(true);
        $item22->addChild($item222);

        $sale->addItem($item2);

        $item3 = Fixtures::createOrderItem(6, 11.36, [15], [7]);
        $sale->addItem($item3);

        $item31 = Fixtures::createOrderItem(2, 4.14)->setPrivate(true);
        $item3->addChild($item31);

        $sale->addItem($item3);

        $sale->addAdjustment(Fixtures::createOrderDiscountAdjustment(12));
        $sale->addAdjustment(Fixtures::createOrderTaxationAdjustment(20));
        $sale->setShipmentAmount(15.26);

        $result = $this->calculator->calculateSaleItem($item1, null, self::C, false);
        $this->assertResult($result, 32.59, 97.77, 6.84, 90.93, 18.19, 109.12);

        $result = $this->calculator->calculateSaleItem($item2, null, self::C, false);
        $this->assertResult($result, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

        $result = $this->calculator->calculateSaleItem($item21, null, self::C, false);
        $this->assertResult($result, 12.34, 246.80, 12.34, 234.46, 12.90, 247.36);

        $result = $this->calculator->calculateSaleItem($item22, null, self::C, false);
        $this->assertResult($result, 70.94, 567.52, 56.75, 510.77, 28.09, 538.86);

        $result = $this->calculator->calculateSaleItem($item3, null, self::C, false);
        $this->assertResult($result, 19.64, 117.84, 17.68, 100.16, 7.01, 107.17);

        $result = $this->calculator->calculateSale($sale, self::C, false);

        $this->assertResult($result, 936.32, 936.32, 112.36, 839.22, 61.30, 900.52);

        $taxes = $result->getTaxAdjustments();
        $this->assertCount(3, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 5.5%', 36.07, 5.5);
        $this->assertAdjustment($taxes[1], 'VAT 7%', 6.17, 7);
        $this->assertAdjustment($taxes[2], 'VAT 20%', 19.06, 20);
    }

    public function test_calculateSale2()
    {
        $sale = Fixtures::createOrder(self::C);

        $item1 = Fixtures::createOrderItem(3, 249.99167, [], [20])->setSale($sale);
        $item2 = Fixtures::createOrderItem(3, 59.48333, [], [20])->setSale($sale);
        $item3 = Fixtures::createOrderItem(3, 42.48333, [], [20])->setSale($sale);
        $item4 = Fixtures::createOrderItem(3, 12.74167, [], [20])->setSale($sale);
        $item5 = Fixtures::createOrderItem(3, 15.29167, [], [20])->setSale($sale);

        $this->calculator->calculateSale($sale);

        $this->assertResult($item1->getResult(self::C), 249.99, 749.97, 0, 749.97, 149.99, 899.96);
        $this->assertResult($item2->getResult(self::C), 59.48, 178.44, 0, 178.44, 35.69, 214.13);
        $this->assertResult($item3->getResult(self::C), 42.48, 127.44, 0, 127.44, 25.49, 152.93);
        $this->assertResult($item4->getResult(self::C), 12.74, 38.22, 0, 38.22, 7.64, 45.86);
        $this->assertResult($item5->getResult(self::C), 15.29, 45.87, 0, 45.87, 9.17, 55.04);

        $grossResult = $sale->getGrossResult(self::C);

        $taxes = $grossResult->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 227.98, 20);

        $this->assertCount(0, $grossResult->getDiscountAdjustments());

        $finalResult = $sale->getFinalResult(self::C);

        $this->assertResult($grossResult, 1139.94, 1139.94, 0, 1139.94, 227.98, 1367.92);
        $this->assertResult($finalResult, 1139.94, 1139.94, 0, 1139.94, 227.98, 1367.92);

        $taxes = $finalResult->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 227.98, 20);
    }

    public function test_calculateSale_withDifferentCurrency()
    {
        $sale = Fixtures::createOrder(self::C);

        $c = 'USD';

        $item1 = Fixtures::createOrderItem(3, 249.99167, [], [20])->setSale($sale);
        $item2 = Fixtures::createOrderItem(3, 59.48333, [], [20])->setSale($sale);
        $item3 = Fixtures::createOrderItem(3, 42.48333, [], [20])->setSale($sale);
        $item4 = Fixtures::createOrderItem(3, 12.74167, [], [20])->setSale($sale);
        $item5 = Fixtures::createOrderItem(3, 15.29167, [], [20])->setSale($sale);

        $this->calculator->calculateSale($sale, $c);

        $this->assertResult($item1->getResult($c), 312.49, 937.47, 0, 937.47, 187.49, 1124.96);
        $this->assertResult($item2->getResult($c), 74.35, 223.05, 0, 223.05, 44.61, 267.66);
        $this->assertResult($item3->getResult($c), 53.10, 159.30, 0, 159.30, 31.86, 191.16);
        $this->assertResult($item4->getResult($c), 15.93, 47.79, 0, 47.79, 9.56, 57.35);
        $this->assertResult($item5->getResult($c), 19.11, 57.33, 0, 57.33, 11.47, 68.80);

        $grossResult = $sale->getGrossResult($c);

        $taxes = $grossResult->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 284.99, 20);

        $this->assertCount(0, $grossResult->getDiscountAdjustments());

        $finalResult = $sale->getFinalResult($c);

        $this->assertResult($grossResult, 1424.94, 1424.94, 0, 1424.94, 284.99, 1709.93);
        $this->assertResult($finalResult, 1424.94, 1424.94, 0, 1424.94, 284.99, 1709.93);

        $taxes = $finalResult->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 284.99, 20);
    }

    /**
     * @covers AmountCalculator::calculateSale()
     */
    public function test_calculateSale_withRootPrivateItem()
    {
        $sale = Fixtures::createOrder(self::C);

        $item1 = Fixtures::createOrderItem(3, 32.59, [7], [20]);
        $sale->addItem($item1);

        $item2 = Fixtures::createOrderItem(4, 12.34)->setPrivate(true);
        $sale->addItem($item2);

        $this->expectException(LogicException::class);

        $this->calculator->calculateSale($sale);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateSaleItem()
    {
        $item = Fixtures::createOrderItem(5, 12.34)->setOrder(Fixtures::createOrder(self::C));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $this->assertResult($item->getResult(self::C), 12.34, 61.70, 0.00, 61.70, 0.00, 61.70);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateSaleItem_withTax()
    {
        $item = Fixtures::createOrderItem(5, 12.34, [], [5.5])->setOrder(Fixtures::createOrder(self::C));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $result = $item->getResult(self::C);
        $this->assertResult($result, 12.34, 61.70, 0.00, 61.70, 3.39, 65.09);

        $taxes = $result->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 5.5%', 3.39, 5.5);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateSaleItem_withTaxes()
    {
        $item = Fixtures::createOrderItem(5, 12.34, [], [5.5, 7])->setOrder(Fixtures::createOrder(self::C));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $result = $item->getResult(self::C);
        $this->assertResult($result, 12.34, 61.70, 0.00, 61.70, 7.71, 69.41);

        $taxes = $result->getTaxAdjustments();
        $this->assertCount(2, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 5.5%', 3.39, 5.5);
        $this->assertAdjustment($taxes[1], 'VAT 7%', 4.32, 7);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateSaleItem_withDiscount()
    {
        $item = Fixtures::createOrderItem(5, 12.34, [5])->setOrder(Fixtures::createOrder(self::C));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $result = $item->getResult(self::C);
        $this->assertResult($result, 12.34, 61.70, 3.08, 58.62, 0.00, 58.62);

        $discounts = $result->getDiscountAdjustments();
        $this->assertCount(1, $discounts);
        $this->assertAdjustment($discounts[0], 'Discount 5%', 3.08, 5);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateSaleItem_withDiscounts()
    {
        $item = Fixtures::createOrderItem(5, 12.34, [5, 15])->setOrder(Fixtures::createOrder(self::C));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $result = $item->getResult(self::C);
        $this->assertResult($result, 12.34, 61.70, 11.87, 49.83, 0.00, 49.83);

        $discounts = $result->getDiscountAdjustments();
        $this->assertCount(2, $discounts);
        $this->assertAdjustment($discounts[0], 'Discount 5%', 3.08, 5);
        $this->assertAdjustment($discounts[1], 'Discount 15%', 8.79, 15);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateSaleItem_withDiscountsAndTaxes()
    {
        $item = Fixtures::createOrderItem(5, 12.34, [5, 15], [5.5, 7])->setOrder(Fixtures::createOrder(self::C));

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $this->assertResult($item->getResult(self::C), 12.34, 61.70, 11.87, 49.83, 6.23, 56.06);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateSaleItem_withTaxGroupMissMatch()
    {
        $taxGroups = Fixtures::getTaxGroups();
        if (2 > count($taxGroups)) {
            throw new \RuntimeException("At least 2 tax groups needed.");
        }

        $item = Fixtures::createOrderItem(3, 0)
            ->setOrder(Fixtures::createOrder(self::C))
            ->setTaxGroup($taxGroups[0]);

        $private = Fixtures::createOrderItem(5, 12.34)
            ->setTaxGroup($taxGroups[1])
            ->setPrivate(true);

        $item->addChild($private);

        $this->expectException(LogicException::class);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateParentSaleItem_withPublicChildren()
    {
        $item = Fixtures::createOrderItem(3, 32.59, [7], [20])->setOrder(Fixtures::createOrder(self::C));

        $public1 = Fixtures::createOrderItem(5, 12.34, [5], [5.5]);
        $item->addChild($public1);

        $public2 = Fixtures::createOrderItem(8, 47.99, [10], [5.5]);
        $item->addChild($public2);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $this->assertResult($item->getResult(self::C), 32.59, 97.77, 6.84, 90.93, 18.19, 109.12);
        $this->assertResult($public1->getResult(self::C), 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);
        $this->assertResult($public2->getResult(self::C), 47.99, 1151.76, 115.18, 1036.58, 57.01, 1093.59);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateParentSaleItem_withPublicAndPrivateChildren()
    {
        $item = Fixtures::createOrderItem(3, 65.78, [10], [5.5])->setOrder(Fixtures::createOrder(self::C));

        $public1 = Fixtures::createOrderItem(5, 12.34, [5], [5.5]);
        $item->addChild($public1);

        $private1 = Fixtures::createOrderItem(2, 47.99)->setPrivate(true);
        $item->addChild($private1);

        $private2 = Fixtures::createOrderItem(4, 18.99)->setPrivate(true);
        $item->addChild($private2);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $this->assertResult($item->getResult(self::C), 237.72, 713.16, 71.32, 641.84, 35.30, 677.14);
        $this->assertResult($public1->getResult(self::C), 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateParentSaleItem_withPrivateChildren()
    {
        $item = Fixtures::createOrderItem(3, 65.78, [10], [5.5])->setOrder(Fixtures::createOrder(self::C));

        $private1 = Fixtures::createOrderItem(2, 47.99)->setPrivate(true);
        $item->addChild($private1);

        $private2 = Fixtures::createOrderItem(4, 18.99)->setPrivate(true);
        $item->addChild($private2);

        $private21 = Fixtures::createOrderItem(1, 5.99)->setPrivate(true);
        $private2->addChild($private21);

        $private22 = Fixtures::createOrderItem(2, 3.99)->setPrivate(true);
        $private2->addChild($private22);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $this->assertResult($item->getResult(self::C), 293.6, 880.8, 88.08, 792.72, 43.60, 836.32);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateCompoundSaleItem_withPublicChildren()
    {
        $item = Fixtures::createOrderItem(3, 0)->setOrder(Fixtures::createOrder());

        $public1 = Fixtures::createOrderItem(5, 12.34, [5], [5.5]);
        $item->addChild($public1);

        $public2 = Fixtures::createOrderItem(8, 47.99, [10], [5.5]);
        $item->addChild($public2);

        // TODO Add child to public 2

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $this->assertResult($item->getResult(self::C), 0, 0, 0, 0, 0, 0);
        $this->assertResult($public1->getResult(self::C), 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);
        $this->assertResult($public2->getResult(self::C), 47.99, 1151.76, 115.18, 1036.58, 57.01, 1093.59);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateCompoundSaleItem_withPublicAndPrivateChildren()
    {
        $item = Fixtures::createOrderItem(3, 0, [10], [5.5])->setOrder(Fixtures::createOrder());

        $public1 = Fixtures::createOrderItem(5, 12.34, [5], [5.5]);
        $item->addChild($public1);

        $private1 = Fixtures::createOrderItem(8, 47.99)->setPrivate(true);
        $item->addChild($private1);

        $private2 = Fixtures::createOrderItem(3, 18.99)->setPrivate(true);
        $item->addChild($private2);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $this->assertResult($item->getResult(self::C), 440.89, 1322.67, 132.27, 1190.40, 65.47, 1255.87);
        $this->assertResult($public1->getResult(self::C), 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateCompoundSaleItem_withPrivateChildren()
    {
        $item = Fixtures::createOrderItem(3, 0, [15], [20])->setOrder(Fixtures::createOrder());

        $private1 = Fixtures::createOrderItem(5, 12.34)->setPrivate(true);
        $item->addChild($private1);

        $private2 = Fixtures::createOrderItem(8, 47.99)->setPrivate(true);
        $item->addChild($private2);

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->calculator->calculateSaleItem($item);

        $this->assertResult($item->getResult(self::C), 445.62, 1336.86, 200.53, 1136.33, 227.27, 1363.6);
    }

    /**
     * @covers AmountCalculator::calculateSaleItem()
     */
    public function test_calculateCompoundSaleItem_withPublicAndPrivateChildren_withoutCache()
    {
        $item = Fixtures::createOrderItem(3, 0, [10], [5.5])->setOrder(Fixtures::createOrder());

        $public1 = Fixtures::createOrderItem(5, 12.34, [5], [5.5]);
        $item->addChild($public1);

        $private1 = Fixtures::createOrderItem(8, 47.99)->setPrivate(true);
        $item->addChild($private1);

        $private2 = Fixtures::createOrderItem(3, 18.99)->setPrivate(true);
        $item->addChild($private2);

        /** @noinspection PhpUnhandledExceptionInspection */
        $result = $this->calculator->calculateSaleItem($item, null, self::C, false);
        $this->assertResult($result, 440.89, 1322.67, 132.27, 1190.40, 65.47, 1255.87);

        $result = $this->calculator->calculateSaleItem($public1, null, self::C, false);
        $this->assertResult($result, 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);
    }

    // TODO Sample sale case tests
}
