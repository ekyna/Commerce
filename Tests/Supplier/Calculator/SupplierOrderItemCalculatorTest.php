<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Supplier\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderItemCalculator;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Commerce\Tests\Fixture;

/**
 * Class SupplierOrderItemCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemCalculatorTest extends AbstractTestCase
{
    private SupplierOrderItemCalculator|null $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new SupplierOrderItemCalculator(
            $this->weightingCalculator,
            $this->getCurrencyConverter(),
        );
    }

    protected function tearDown(): void
    {
        $this->calculator = null;

        parent::tearDown();
    }

    /**
     * @param array $data The supplier order data
     * @param array $expected The expected calculation result
     *
     * @dataProvider \Ekyna\Component\Commerce\Tests\Supplier\Calculator\DataProvider::provideData
     */
    public function testCalculateItemProductPrice(array $data, array $expected): void
    {
        Fixture::supplierOrder($data);

        $this->configureWeightingCalculator($data);

        foreach ($expected['item'] as $ref => $result) {
            /** @var SupplierOrderItemInterface $item */
            $item = Fixture::get($ref);

            self::assertEquals(
                new Decimal((string)$result['price']),
                $this->calculator->calculateItemProductPrice($item)
            );
        }
    }

    /**
     * @param array $data The supplier order data
     * @param array $expected The expected calculation result
     *
     * @dataProvider \Ekyna\Component\Commerce\Tests\Supplier\Calculator\DataProvider::provideData
     */
    public function testCalculateItemShippingPrice(array $data, array $expected): void
    {
        Fixture::supplierOrder($data);

        $this->configureWeightingCalculator($data);

        foreach ($expected['item'] as $ref => $result) {
            /** @var SupplierOrderItemInterface $item */
            $item = Fixture::get($ref);

            self::assertEquals(
                new Decimal((string)$result['shipping']),
                $this->calculator->calculateItemShippingPrice($item)
            );
        }
    }
}
