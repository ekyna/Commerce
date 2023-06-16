<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Supplier\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculator;
use Ekyna\Component\Commerce\Tests\Fixture;

/**
 * Class SupplierOrderCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderCalculatorTest extends AbstractTestCase
{
    private SupplierOrderCalculator|null $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new SupplierOrderCalculator(
            $this->weightingCalculator,
            $this->getTaxResolverMock(),
            self::DEFAULT_CURRENCY,
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
    public function testCalculator(array $data, array $expected): void
    {
        $order = Fixture::supplierOrder($data);

        $this->configureWeightingCalculator($data);
        $this->configureTaxResolver($data['_tax_resolver']);

        self::assertEquals(
            new Decimal((string)$expected['order']['total']),
            $this->calculator->calculatePaymentTotal($order)
        );
        self::assertEquals(
            new Decimal((string)$expected['order']['tax']),
            $this->calculator->calculatePaymentTax($order)
        );
        self::assertEquals(
            new Decimal((string)$expected['order']['items']),
            $this->calculator->calculateItemsTotal($order)
        );
        self::assertEquals(
            new Decimal((string)$expected['order']['forwarder']),
            $this->calculator->calculateForwarderTotal($order)
        );
        self::assertEquals(
            new Decimal((string)$expected['order']['weight']),
            $this->calculator->calculateWeightTotal($order)
        );
    }

    /**
     * Configures the tax resolver.
     *
     * @param array $config
     */
    private function configureTaxResolver(array $config): void
    {
        foreach ($config as &$data) {
            $data['taxable'] = Fixture::get($data['taxable']);
            $data['context'] = Fixture::get($data['context']);

            foreach ($data['taxes'] as $index => $tax) {
                $data['taxes'][$index] = Fixture::get($tax);
            }
        }

        $this
            ->getTaxResolverMock()
            ->method('resolveTaxes')
            ->willReturnCallback(function ($taxable, $context) use ($config) {
                foreach ($config as $data) {
                    if ($data['taxable'] === $taxable && $data['context'] === $context) {
                        return $data['taxes'];
                    }
                }

                return [];
            });
    }
}
