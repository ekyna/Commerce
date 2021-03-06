<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\AmountCalculator;
use Ekyna\Component\Commerce\Common\Calculator\AmountCalculatorFactory;
use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Stat\Calculator\StatFilter;
use Ekyna\Component\Commerce\Tests\Common\Model\AbstractAmountTest;
use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AmountCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AmountCalculatorTest extends AbstractAmountTest
{
    /**
     * @var InvoiceSubjectCalculatorInterface|MockObject
     */
    private $invoiceCalculator;


    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->invoiceCalculator = $this->createMock(InvoiceSubjectCalculatorInterface::class);
    }

    /**
     * Returns a new instance of amount calculator.
     *
     * @param string          $currency
     * @param bool            $revenue
     * @param StatFilter|null $filter
     *
     * @return AmountCalculator
     */
    private function createCalculator(
        string $currency,
        bool $revenue = false,
        StatFilter $filter = null
    ): AmountCalculator {
        $calculator = new AmountCalculator($currency, $revenue, $filter);

        $calculator->setCurrencyConverter($this->getCurrencyConverter());
        $calculator->setInvoiceCalculator($this->invoiceCalculator);

        $factory = $this->createMock(AmountCalculatorFactory::class);
        $factory
            ->method('create')
            ->willReturnCallback(function ($currency, $revenue) use ($factory) {
                $c = new AmountCalculator($currency ?? Fixture::CURRENCY_EUR, $revenue);

                $c->setCurrencyConverter($this->getCurrencyConverter());
                $c->setInvoiceCalculator($this->invoiceCalculator);
                $c->setAmountCalculatorFactory($factory);

                return $c;
            });

        $calculator->setAmountCalculatorFactory($factory);

        return $calculator;
    }

    /**
     * @inheritDoc
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->invoiceCalculator = null;
    }

    public function test_calculateSale(): void
    {
        $sale = Fixture::order(Data::order1());

        $c = Fixture::CURRENCY_EUR;

        $calculator = $this->createCalculator($c);

        // Items
        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item1'));
        $this->assertResult($result, 32.59, 97.77, 6.84, 90.93, 18.19, 109.12);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item2'));
        $this->assertResult($result, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item2_1'));
        $this->assertResult($result, 12.34, 246.80, 12.34, 234.46, 12.90, 247.36);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item2_2'));
        $this->assertResult($result, 70.94, 567.52, 56.75, 510.77, 28.09, 538.86);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item3'));
        $this->assertResult($result, 19.64, 117.84, 17.68, 100.16, 7.01, 107.17);

        // Gross result
        $grossResult = $calculator->calculateSale($sale, true);

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

        $discountResult = $calculator->calculateSaleDiscount($sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT)[0]);
        $shipmentResult = $calculator->calculateSaleShipment($sale);
        $finalResult = $calculator->calculateSale($sale);

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

    /** @noinspection PhpParamsInspection */
    public function test_calculateSale_cached(): void
    {
        $sale = Fixture::order(Data::order1());

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR, false);

        $calculator->calculateSale($sale);

        $result = $calculator->calculateSaleItem(Fixture::get('order1_item1'));
        $this->assertResult($result, 32.59, 97.77, 6.84, 90.93, 18.19, 109.12);

        $result = $calculator->calculateSaleItem(Fixture::get('order1_item2'));
        $this->assertResult($result, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

        $result = $calculator->calculateSaleItem(Fixture::get('order1_item2_1'));
        $this->assertResult($result, 12.34, 246.80, 12.34, 234.46, 12.90, 247.36);

        $result = $calculator->calculateSaleItem(Fixture::get('order1_item2_2'));
        $this->assertResult($result, 70.94, 567.52, 56.75, 510.77, 28.09, 538.86);

        $result = $calculator->calculateSaleItem(Fixture::get('order1_item3'));
        $this->assertResult($result, 19.64, 117.84, 17.68, 100.16, 7.01, 107.17);

        $result = $calculator->calculateSale($sale);

        $this->assertResult($result, 936.32, 936.32, 112.36, 839.22, 61.30, 900.52);

        $taxes = $result->getTaxAdjustments();
        $this->assertCount(3, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 5.5%', 36.07, 5.5);
        $this->assertAdjustment($taxes[1], 'VAT 7%', 6.17, 7);
        $this->assertAdjustment($taxes[2], 'VAT 20%', 19.06, 20);
    }

    public function test_calculateSale_revenue(): void
    {
        $sale = Fixture::order(Data::order1());

        $this
            ->invoiceCalculator
            ->method('calculateInvoicedQuantity')
            ->willReturnCallback(function ($subject) {
                if ($subject instanceof SaleItemInterface) {
                    return $subject->getTotalQuantity();
                }

                return 1;
            });

        $creditMap = [
            Fixture::get('order1_item1')->getId()     => 0.,
            Fixture::get('order1_item2')->getId()     => 1.,
            Fixture::get('order1_item2_1')->getId()   => 5.,
            Fixture::get('order1_item2_2')->getId()   => 2.,
            Fixture::get('order1_item2_2_1')->getId() => 4.,
            Fixture::get('order1_item2_2_2')->getId() => 6.,
            Fixture::get('order1_item3')->getId()     => 4.,
            Fixture::get('order1_item3_1')->getId()   => 8.,
        ];

        $this
            ->invoiceCalculator
            ->method('calculateCreditedQuantity')
            ->willReturnCallback(function ($subject) use ($creditMap) {
                if ($subject instanceof SaleItemInterface) {
                    return $creditMap[$subject->getId()];
                }

                return 0;
            });

        $this
            ->invoiceCalculator
            ->method('calculateSoldQuantity')
            ->willReturnCallback(function ($subject) use ($creditMap) {
                if ($subject instanceof SaleItemInterface) {
                    return $subject->getTotalQuantity() - $creditMap[$subject->getId()];
                }

                return 1;
            });

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR, true);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item1'));
        $this->assertResult($result, 32.59, 97.77, 6.84, 90.93, 18.19, 109.12);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item2'));
        $this->assertResult($result, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item2_1'));
        $this->assertResult($result, 12.34, 185.10, 9.25, 175.85, 9.67, 185.52);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item2_2'));
        $this->assertResult($result, 70.94, 425.64, 42.56, 383.08, 21.07, 404.15);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order1_item3'));
        $this->assertResult($result, 19.64, 39.28, 5.89, 33.39, 2.34, 35.73);

        $grossResult = $calculator->calculateSale($sale, true);

        $taxes = $grossResult->getTaxAdjustments();
        $this->assertCount(3, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 18.19, 20);
        $this->assertAdjustment($taxes[1], 'VAT 5.5%', 30.74, 5.5);
        $this->assertAdjustment($taxes[2], 'VAT 7%', 2.34, 7);

        $discounts = $grossResult->getDiscountAdjustments();
        $this->assertCount(4, $discounts);
        $this->assertAdjustment($discounts[0], 'Discount 7%', 6.84, 7);
        $this->assertAdjustment($discounts[1], 'Discount 5%', 9.25, 5);
        $this->assertAdjustment($discounts[2], 'Discount 10%', 42.56, 10);
        $this->assertAdjustment($discounts[3], 'Discount 15%', 5.89, 15);

        $discountResult = $calculator->calculateSaleDiscount($sale->getAdjustments(AdjustmentTypes::TYPE_DISCOUNT)[0]);
        $shipmentResult = $calculator->calculateSaleShipment($sale);
        $finalResult = $calculator->calculateSale($sale);

        $this->assertResult($grossResult, 747.79, 747.79, 64.54, 683.25, 51.27, 734.52);

        $this->assertResult($discountResult, 81.99, 81.99, 0.00, 81.99, 6.15, 88.14);

        $this->assertResult($shipmentResult, 15.26, 15.26, 0.00, 15.26, 3.05, 18.31);

        $this->assertResult($finalResult, 683.25, 683.25, 81.99, 616.52, 48.17, 664.69);

        $taxes = $finalResult->getTaxAdjustments();
        $this->assertCount(3, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 5.5%', 27.05, 5.5);
        $this->assertAdjustment($taxes[1], 'VAT 7%', 2.06, 7);
        $this->assertAdjustment($taxes[2], 'VAT 20%', 19.06, 20);
    }

    public function test_calculateSale2(): void
    {
        $sale = Fixture::order(Data::order2());

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item1'));
        $this->assertResult($result, 249.99, 749.97, 0, 749.97, 149.99, 899.96);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item2'));
        $this->assertResult($result, 59.48, 178.44, 0, 178.44, 35.69, 214.13);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item3'));
        $this->assertResult($result, 42.48, 127.44, 0, 127.44, 25.49, 152.93);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item4'));
        $this->assertResult($result, 12.74, 38.22, 0, 38.22, 7.64, 45.86);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item5'));
        $this->assertResult($result, 15.29, 45.87, 0, 45.87, 9.17, 55.04);

        $grossResult = $calculator->calculateSale($sale, true);

        $taxes = $grossResult->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 227.98, 20);

        $this->assertCount(0, $grossResult->getDiscountAdjustments());

        $finalResult = $calculator->calculateSale($sale);

        $this->assertResult($grossResult, 1139.94, 1139.94, 0, 1139.94, 227.98, 1367.92);
        $this->assertResult($finalResult, 1139.94, 1139.94, 0, 1139.94, 227.98, 1367.92);

        $taxes = $finalResult->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 227.98, 20);
    }

    public function test_calculateSale3(): void
    {
        $sale = Fixture::order(Data::order3());

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order3_item1'));
        $this->assertResult($result, 78.20, 312.80, 0, 312.80, 31.28, 344.08);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order3_item2'));
        $this->assertResult($result, 69.50, 695.00, 83.40, 611.60, 61.16, 672.76);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order3_item3_1'));
        $this->assertResult($result, 82.60, 826.00, 41.30, 784.70, 156.94, 941.64);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order3_item3_2'));
        $this->assertResult($result, 165.00, 660.00, 79.20, 580.80, 116.16, 696.96);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order3_item3'));
        $this->assertResult($result, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00);

        $grossResult = $calculator->calculateSale($sale, true);
        $this->assertResult($grossResult, 2493.80, 2493.80, 203.90, 2289.90, 365.54, 2655.44);

        $taxes = $grossResult->getTaxAdjustments();
        $this->assertCount(2, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 10%', 92.44, 10);
        $this->assertAdjustment($taxes[1], 'VAT 20%', 273.10, 20);

        $discounts = $grossResult->getDiscountAdjustments();
        $this->assertCount(2, $discounts);
        $this->assertAdjustment($discounts[0], 'Discount 12%', 162.6, 12);
        $this->assertAdjustment($discounts[1], 'Discount 5%', 41.3, 5);

        $shipmentResult = $calculator->calculateSaleShipment($sale);
        $this->assertResult($shipmentResult, 12.34, 12.34, 0, 12.34, 2.47, 14.81);

        $finalResult = $calculator->calculateSale($sale);
        $this->assertResult($finalResult, 2289.90, 2289.90, 0, 2302.24, 368.01, 2670.25);

        $taxes = $finalResult->getTaxAdjustments();
        $this->assertCount(2, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 10%', 92.44, 10);
        $this->assertAdjustment($taxes[1], 'VAT 20%', 275.57, 20);
    }

    public function test_calculateSale_withDifferentCurrency(): void
    {
        $sale = Fixture::order(Data::order2());

        $calculator = $this->createCalculator(Fixture::CURRENCY_USD);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item1'));
        $this->assertResult($result, 312.49, 937.47, 0, 937.47, 187.49, 1124.96);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item2'));
        $this->assertResult($result, 74.35, 223.05, 0, 223.05, 44.61, 267.66);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item3'));
        $this->assertResult($result, 53.10, 159.30, 0, 159.30, 31.86, 191.16);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item4'));
        $this->assertResult($result, 15.93, 47.79, 0, 47.79, 9.56, 57.35);

        $result = $calculator->calculateSaleItem(Fixture::orderItem('order2_item5'));
        $this->assertResult($result, 19.11, 57.33, 0, 57.33, 11.47, 68.80);

        $grossResult = $calculator->calculateSale($sale, true);

        $taxes = $grossResult->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 284.99, 20);

        $this->assertCount(0, $grossResult->getDiscountAdjustments());

        $finalResult = $calculator->calculateSale($sale);

        $this->assertResult($grossResult, 1424.94, 1424.94, 0, 1424.94, 284.99, 1709.93);
        $this->assertResult($finalResult, 1424.94, 1424.94, 0, 1424.94, 284.99, 1709.93);

        $taxes = $finalResult->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 20%', 284.99, 20);
    }

    public function test_calculateSale_withRootPrivateItem(): void
    {
        $sale = Fixture::order([
            'items' => [
                [
                    'quantity'  => 3,
                    'price'     => 32.59,
                    'discounts' => [7],
                    'taxes'     => [20],
                ],
                [
                    'quantity' => 4,
                    'price'    => 12.34,
                    'private'  => true,
                ],
            ],
        ]);

        $this->expectException(LogicException::class);

        $this->createCalculator(Fixture::CURRENCY_EUR)->calculateSale($sale);
    }

    public function test_calculateSaleItem(): void
    {
        $item = Fixture::orderItem([
            'quantity' => 5,
            'price'    => 12.34,
        ])->setOrder(Fixture::order());

        $result = $this->createCalculator(Fixture::CURRENCY_EUR)->calculateSaleItem($item);

        $this->assertResult($result, 12.34, 61.70, 0.00, 61.70, 0.00, 61.70);
    }

    public function test_calculateSaleItem_withTax(): void
    {
        $item = Fixture::orderItem([
            'quantity' => 5,
            'price'    => 12.34,
            'taxes'    => [5.5],
        ])->setOrder(Fixture::order());

        $result = $this->createCalculator(Fixture::CURRENCY_EUR)->calculateSaleItem($item);

        $this->assertResult($result, 12.34, 61.70, 0.00, 61.70, 3.39, 65.09);

        $taxes = $result->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 5.5%', 3.39, 5.5);
    }

    public function test_calculateSaleItem_withTaxes(): void
    {
        $item = Fixture::orderItem([
            'quantity' => 5,
            'price'    => 12.34,
            'taxes'    => [5.5, 7],
        ])->setOrder(Fixture::order());

        $result = $this->createCalculator(Fixture::CURRENCY_EUR)->calculateSaleItem($item);

        $this->assertResult($result, 12.34, 61.70, 0.00, 61.70, 7.71, 69.41);

        $taxes = $result->getTaxAdjustments();
        $this->assertCount(2, $taxes);
        $this->assertAdjustment($taxes[0], 'VAT 5.5%', 3.39, 5.5);
        $this->assertAdjustment($taxes[1], 'VAT 7%', 4.32, 7);
    }

    public function test_calculateSaleItem_withDiscount(): void
    {
        $item = Fixture::orderItem([
            'quantity'  => 5,
            'price'     => 12.34,
            'discounts' => [5],
        ])->setOrder(Fixture::order());

        $result = $this->createCalculator(Fixture::CURRENCY_EUR)->calculateSaleItem($item);

        $this->assertResult($result, 12.34, 61.70, 3.08, 58.62, 0.00, 58.62);

        $discounts = $result->getDiscountAdjustments();
        $this->assertCount(1, $discounts);
        $this->assertAdjustment($discounts[0], 'Discount 5%', 3.08, 5);
    }

    public function test_calculateSaleItem_withDiscounts(): void
    {
        $item = Fixture::orderItem([
            'quantity'  => 5,
            'price'     => 12.34,
            'discounts' => [5, 15],
        ])->setOrder(Fixture::order());

        $result = $this->createCalculator(Fixture::CURRENCY_EUR)->calculateSaleItem($item);

        $this->assertResult($result, 12.34, 61.70, 11.87, 49.83, 0.00, 49.83);

        $discounts = $result->getDiscountAdjustments();
        $this->assertCount(2, $discounts);
        $this->assertAdjustment($discounts[0], 'Discount 5%', 3.08, 5);
        $this->assertAdjustment($discounts[1], 'Discount 15%', 8.79, 15);
    }

    public function test_calculateSaleItem_withDiscountsAndTaxes(): void
    {
        $item = Fixture::orderItem([
            'quantity'  => 5,
            'price'     => 12.34,
            'discounts' => [5, 15],
            'taxes'     => [5.5, 7],
        ])->setOrder(Fixture::order());

        $result = $this->createCalculator(Fixture::CURRENCY_EUR)->calculateSaleItem($item);

        $this->assertResult($result, 12.34, 61.70, 11.87, 49.83, 6.23, 56.06);
    }

    public function test_calculateSaleItem_withTaxGroupMissMatch(): void
    {
        $item = Fixture::orderItem([
            'order'     => [],
            'tax_group' => Fixture::TAX_GROUP_NORMAL,
            'quantity'  => 3,
            'price'     => 0,
            'children'  => [
                [
                    'tax_group' => Fixture::TAX_GROUP_INTERMEDIATE,
                    'quantity'  => 5,
                    'price'     => 12.34,
                    'private'   => true,
                ],
            ],
        ]);

        $this->expectException(LogicException::class);

        $this->createCalculator(Fixture::CURRENCY_EUR)->calculateSaleItem($item);
    }

    public function test_calculateParentSaleItem_withPublicChildren(): void
    {
        $item = Fixture::orderItem([
            'order'     => [],
            'quantity'  => 3,
            'price'     => 32.59,
            'discounts' => [7],
            'taxes'     => [20],
        ]);

        $public1 = Fixture::orderItem([
            'parent'    => $item,
            'quantity'  => 5,
            'price'     => 12.34,
            'discounts' => [5],
            'taxes'     => [5.5],
        ]);

        $public2 = Fixture::orderItem([
            'parent'    => $item,
            'quantity'  => 8,
            'price'     => 47.99,
            'discounts' => [10],
            'taxes'     => [5.5],
        ]);

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem($item);
        $this->assertResult($result, 32.59, 97.77, 6.84, 90.93, 18.19, 109.12);

        $result = $calculator->calculateSaleItem($public1);
        $this->assertResult($result, 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);

        $result = $calculator->calculateSaleItem($public2);
        $this->assertResult($result, 47.99, 1151.76, 115.18, 1036.58, 57.01, 1093.59);
    }

    public function test_calculateParentSaleItem_withPublicAndPrivateChildren(): void
    {
        $item = Fixture::orderItem([
            'order'     => [],
            'quantity'  => 3,
            'price'     => 65.78,
            'discounts' => [10],
            'taxes'     => [5.5],
            'children'  => [
                [
                    'quantity' => 2,
                    'price'    => 47.99,
                    'private'  => true,
                ],
                [
                    'quantity' => 4,
                    'price'    => 18.99,
                    'private'  => true,
                ],
            ],
        ]);

        $public1 = Fixture::orderItem([
            'parent'    => $item,
            'quantity'  => 5,
            'price'     => 12.34,
            'discounts' => [5],
            'taxes'     => [5.5],
        ]);

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem($item);
        $this->assertResult($result, 237.72, 713.16, 71.32, 641.84, 35.30, 677.14);

        $result = $calculator->calculateSaleItem($public1);
        $this->assertResult($result, 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);
    }

    public function test_calculateParentSaleItem_withPrivateChildren(): void
    {
        $item = Fixture::orderItem([
            'order'     => [],
            'quantity'  => 3,
            'price'     => 65.78,
            'discounts' => [10],
            'taxes'     => [5.5],
            'children'  => [
                [
                    'quantity' => 2,
                    'price'    => 47.99,
                    'private'  => true,
                ],
                [
                    'quantity' => 4,
                    'price'    => 18.99,
                    'private'  => true,
                    'children' => [
                        [
                            'quantity' => 1,
                            'price'    => 5.99,
                            'private'  => true,
                        ],
                        [
                            'quantity' => 2,
                            'price'    => 3.99,
                            'private'  => true,
                        ],
                    ],
                ],
            ],
        ]);

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem($item);

        $this->assertResult($result, 293.6, 880.8, 88.08, 792.72, 43.60, 836.32);
    }

    public function test_calculateCompoundSaleItem_withPublicChildren(): void
    {
        // TODO Use ->setCompound(true)

        $item = Fixture::orderItem([
            'order'    => [],
            'quantity' => 3,
            'price'    => 0,
        ]);

        $public1 = Fixture::orderItem([
            'parent'    => $item,
            'quantity'  => 5,
            'price'     => 12.34,
            'discounts' => [5],
            'taxes'     => [5.5],
        ]);

        $public2 = Fixture::orderItem([
            'parent'    => $item,
            'quantity'  => 8,
            'price'     => 47.99,
            'discounts' => [10],
            'taxes'     => [5.5],
        ]);

        // TODO Add child(ren) to public 2

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem($item);
        $this->assertResult($result, 0, 0, 0, 0, 0, 0);

        $result = $calculator->calculateSaleItem($public1);
        $this->assertResult($result, 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);

        $result = $calculator->calculateSaleItem($public2);
        $this->assertResult($result, 47.99, 1151.76, 115.18, 1036.58, 57.01, 1093.59);
    }

    public function test_calculateCompoundSaleItem_withPublicAndPrivateChildren(): void
    {
        // TODO Use ->setCompound(true)

        $item = Fixture::orderItem([
            'order'     => [],
            'quantity'  => 3,
            'price'     => 0,
            'discounts' => [10],
            'taxes'     => [5.5],
            'children'  => [
                [
                    'quantity' => 8,
                    'price'    => 47.99,
                    'private'  => true,
                ],
                [
                    'quantity' => 3,
                    'price'    => 18.99,
                    'private'  => true,
                ],
            ],
        ]);

        $public1 = Fixture::orderItem([
            'parent'    => $item,
            'quantity'  => 5,
            'price'     => 12.34,
            'discounts' => [5],
            'taxes'     => [5.5],
        ]);

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem($item);
        $this->assertResult($result, 440.89, 1322.67, 132.27, 1190.40, 65.47, 1255.87);

        $result = $calculator->calculateSaleItem($public1);
        $this->assertResult($result, 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);
    }

    public function test_calculateCompoundSaleItem_withPrivateChildren(): void
    {
        // TODO use ->setCompound(true)

        $item = Fixture::orderItem([
            'order'     => [],
            'quantity'  => 3,
            'discounts' => [15],
            'taxes'     => [20],
            'children'  => [
                [
                    'quantity' => 5,
                    'price'    => 12.34,
                    'private'  => true,
                ],
                [
                    'quantity' => 8,
                    'price'    => 47.99,
                    'private'  => true,
                ],
            ],
        ]);

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem($item);
        $this->assertResult($result, 445.62, 1336.86, 200.53, 1136.33, 227.27, 1363.6);
    }

    public function test_calculateCompoundSaleItem_withPublicAndPrivateChildren_withoutCache(): void
    {
        // TODO use ->setCompound(true)

        $item = Fixture::orderItem([
            'order'     => [],
            'quantity'  => 3,
            'price'     => 0,
            'discounts' => [10],
            'taxes'     => [5.5],
            'children'  => [
                [
                    'quantity' => 8,
                    'price'    => 47.99,
                    'private'  => true,
                ],
                [
                    'quantity' => 3,
                    'price'    => 18.99,
                    'private'  => true,
                ],
            ],
        ]);

        $public1 = Fixture::orderItem([
            'parent'    => $item,
            'quantity'  => 5,
            'price'     => 12.34,
            'discounts' => [5],
            'taxes'     => [5.5],
        ]);

        $calculator = $this->createCalculator(Fixture::CURRENCY_EUR);

        $result = $calculator->calculateSaleItem($item);
        $this->assertResult($result, 440.89, 1322.67, 132.27, 1190.40, 65.47, 1255.87);

        $result = $calculator->calculateSaleItem($public1, null);
        $this->assertResult($result, 12.34, 185.1, 9.25, 175.85, 9.67, 185.52);
    }

    // TODO Sample sale case tests
}
