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
 * @author Étienne Dauvergne <contact@ekyna.com>
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
    }
}
