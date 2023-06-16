<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Calculator;

use Ekyna\Component\Commerce\Common\Calculator\MarginCalculator;
use Ekyna\Component\Commerce\Tests\Common\Model\AbstractMarginTest;
use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\Util;
use Generator;

use function iterator_to_array;

/**
 * Class MarginCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MarginCalculatorTest extends AbstractMarginTest
{
    private MarginCalculator|null $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new MarginCalculator(Fixture::CURRENCY_EUR);
        $this->calculator->setCalculatorFactory($this->amountCalculatorFactory);
        $this->calculator->setItemCostCalculator($this->itemCostCalculator);
        $this->calculator->setShipmentCostCalculator($this->shipmentCostCalculator);
    }

    protected function tearDown(): void
    {
        $this->calculator = null;

        parent::tearDown();
    }

    /**
     * @dataProvider provideSaleItemData
     */
    public function testCalculateSaleItem(array $data, string $reference, array $expected, bool $single): void
    {
        Fixture::order($data);

        $this->configureMocks($data, $single);

        $item = Fixture::get($reference);

        $actual = $this->calculator->calculateSaleItem($item, $single);

        $this->assertMargin($actual, Util::margin($expected, $single));
    }

    public function provideSaleItemData(): Generator
    {
        $order = Data::order1();

        yield from $this->buildSaleItemsData($order, $order['items']);

        $order = Data::order3();

        yield from $this->buildSaleItemsData($order, $order['items']);
    }

    private function buildSaleItemsData(array $order, array $items): Generator
    {
        foreach ($items as $item) {
            yield $item['_reference'] => [
                $order,
                $item['_reference'],
                $item['_margin'],
                false,
            ];

            yield $item['_reference'] . ' (single)' => [
                $order,
                $item['_reference'],
                $item['_margin'],
                true,
            ];

            if (!empty($item['children'])) {
                yield from $this->buildSaleItemsData($order, $item['children']);
            }
        }
    }

    /**
     * @dataProvider provideCalculateSale
     */
    public function testCalculateSale(array $data): void
    {
        $sale = Fixture::order($data);

        $this->configureMocks($data, false);

        $actual = $this->calculator->calculateSale($sale);

        $this->assertMargin($actual, Util::margin($data['_margin']));
    }

    public function provideCalculateSale(): Generator
    {
        yield 'Order 1' => [Data::order1()];

        yield 'Order 3' => [Data::order3()];
        // TODO yield 'Order 4' => [Data::order4()];
    }

    private function configureMocks(array $data, bool $single): void
    {
        $this
            ->amountCalculator
            ->method('calculateSale')
            ->willReturn(Util::amount([]));

        $this
            ->amountCalculator
            ->method('calculateSaleShipment')
            ->willReturn(
                Util::amount($data['_amount']['shipment'])
            );

        $map = iterator_to_array(Util::itemsAmountsMap($data['items'], $single));

        $this
            ->amountCalculator
            ->method('calculateSaleItem')
            ->willReturnMap($map);

        if (!empty($data['discounts'])) {
            $map = iterator_to_array(Util::discountsAmountsMap($data['discounts']));

            $this
                ->amountCalculator
                ->method('calculateSaleDiscount')
                ->willReturnMap($map);
        }

        $map = iterator_to_array(Util::itemsCostsMap($data['items'], $single));

        $this
            ->itemCostCalculator
            ->method('calculateSaleItem')
            ->willReturnMap($map);

        $this
            ->shipmentCostCalculator
            ->method('calculateSale')
            ->with(Fixture::get($data['_reference']))
            ->willReturn(Util::cost($data['_cost']));
    }

    // TODO test filter

    // TODO test cached margins
}
