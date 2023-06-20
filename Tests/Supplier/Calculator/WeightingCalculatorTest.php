<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Supplier\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Calculator\WeightingCalculator;
use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use Generator;
use PHPUnit\Framework\TestCase;

/**
 * Class WeightingCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class WeightingCalculatorTest extends TestCase
{
    private WeightingCalculator|null $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new WeightingCalculator();
    }

    protected function tearDown(): void
    {
        $this->calculator = null;

        parent::tearDown();
    }

    /**
     * @dataProvider provideData
     */
    public function testGetWeighting(array $data): void
    {
        Fixture::supplierOrder($data);

        foreach ($data['items'] as $item) {
            $reference = $item['_reference'];

            $weighting = $this->calculator->getWeighting(Fixture::get($reference));

            $expected = $item['_weighting'];

            self::assertEquals(new Decimal((string)$expected['weight']), $weighting->weight);
            self::assertEquals(new Decimal((string)$expected['price']), $weighting->price);
            self::assertEquals(new Decimal((string)$expected['quantity']), $weighting->quantity);
        }
    }

    private function provideData(): Generator
    {
        yield 'supplier_order_1' => [Data::supplier_order1()];

        yield 'supplier_order_2' => [Data::supplier_order2()];

        yield 'supplier_order_3' => [Data::supplier_order3()];

        yield 'supplier_order_4' => [Data::supplier_order4()];

        yield 'supplier_order_5' => [
            [
                'shipping_cost' => 320.0,
                'items'         => [
                    [
                        '_reference' => 'supplier_order_5_item1',
                        '_weighting' => [
                            'default'  => 0.2,
                            'weight'   => 0,
                            'price'    => 0.2,
                            'quantity' => 0.2,
                        ],
                        'weight'     => 0,
                        'price'      => 12.34,
                        'quantity'   => 5.0,
                        // Total weight:  0 | Weighting: 0,2 (0,2)
                        // Total price: 61.7 | Weighting: 0,2 (0,2)
                        // Discount:    99,78268560165765
                        // Base:        689,9773143983424
                        // Tax:         138.00
                    ],
                ],
            ],
        ];
    }
}
