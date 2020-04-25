<?php

namespace Ekyna\Component\Commerce\Tests\Supplier\Calculator;

use Ekyna\Component\Commerce\Supplier\Calculator\SupplierOrderCalculator;
use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;

/**
 * Class SupplierOrderCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Supplier\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderCalculatorTest extends TestCase
{
    /**
     * @var SupplierOrderCalculator
     */
    private $calculator;

    protected function setUp(): void
    {
        $this->calculator = new SupplierOrderCalculator(
            $this->getCurrencyConverter(),
            $this->getTaxResolverMock(),
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->calculator = null;
    }

    /**
     * @param array $order    The supplier order data
     * @param array $taxes    The tax resolver config
     * @param array $expected The expected calculation result
     *
     * @dataProvider provide_data
     */
    public function test_calculator(array $order, array $taxes, array $expected): void
    {
        $order = Fixture::supplierOrder($order);

        $this->configureTaxResolver($taxes);

        $this->assertSame($expected['total'], $this->calculator->calculatePaymentTotal($order));
        $this->assertSame($expected['tax'], $this->calculator->calculatePaymentTax($order));
        $this->assertSame($expected['items'], $this->calculator->calculateItemsTotal($order));
        $this->assertSame($expected['forwarder'], $this->calculator->calculateForwarderTotal($order));
        $this->assertSame($expected['weight'], $this->calculator->calculateWeightTotal($order));

        foreach ($expected['units'] as $ref => $result) {
            /** @var \Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface $item */
            $item = Fixture::get($ref);
            $this->assertSame($result['price'], $this->calculator->calculateStockUnitNetPrice($item));
            $this->assertSame($result['shipping'], $this->calculator->calculateStockUnitShippingPrice($item));
        }
    }

    public function provide_data(): \Generator
    {
        yield 'FR -> FR, discount, no forwarder' => [
            'order'  => Data::supplier_order1(),
            'taxes'  => [
                [
                    'taxable' => 'supplier_order1_item1',
                    'context' => 'supplier_order1',
                    'taxes'   => [Fixture::TAX_FR_NORMAL],
                ],
                [
                    'taxable' => 'supplier_order1_item2',
                    'context' => 'supplier_order1',
                    'taxes'   => [Fixture::TAX_FR_INTERMEDIATE],
                ],
            ],
            'result' => [
                'total'     => 1593.94,
                'tax'       => 236.72,
                'items'     => 1187.22,
                'forwarder' => 0.,
                'weight'    => 60.3,
                'units'     => [
                    'supplier_order1_item1' => [
                        'price'    => 10.78090,
                        'shipping' => 4.24544,
                    ],
                    'supplier_order1_item2' => [
                        'price'    => 49.60610,
                        'shipping' => 6.89884,
                    ],
                ],
            ],
        ];

        yield 'US -> FR, no discount, no forwarder' => [
            'order'  => Data::supplier_order2(),
            'taxes'  => [
                [
                    'taxable' => 'supplier_order2_item1',
                    'context' => 'supplier_order2',
                    'taxes'   => [],
                ],
                [
                    'taxable' => 'supplier_order2_item2',
                    'context' => 'supplier_order2',
                    'taxes'   => [],
                ],
            ],
            'result' => [
                'total'     => 3143.76,
                'tax'       => 0.,
                'items'     => 2843.76,
                'forwarder' => 0.,
                'weight'    => 79.2,
                'units'     => [
                    'supplier_order2_item1' => [
                        'price'    => 40.77679,
                        'shipping' => 1.69104,
                    ],
                    'supplier_order2_item2' => [
                        'price'    => 20.93750,
                        'shipping' => 3.72028,
                    ],
                ],
            ],
        ];

        yield 'US -> FR, no discount, forwarder' => [
            'order'  => Data::supplier_order3(),
            'taxes'  => [
                [
                    'taxable' => 'supplier_order3_item1',
                    'context' => 'supplier_order3',
                    'taxes'   => [],
                ],
                [
                    'taxable' => 'supplier_order3_item2',
                    'context' => 'supplier_order3',
                    'taxes'   => [],
                ],
            ],
            'result' => [
                'total'     => 8934.14,
                'tax'       => 0.,
                'items'     => 7074.14,
                'forwarder' => 1969.21,
                'weight'    => 107.4,
                'units'     => [
                    'supplier_order3_item1' => [
                        'price'    => 51.97600,
                        'shipping' => 15.04879,
                    ],
                    'supplier_order3_item2' => [
                        'price'    => 60.29600,
                        'shipping' => 26.33538,
                    ],
                ],
            ],
        ];

        yield 'FR -> FR, no discount, no forwarder' => [
            'order'  => Data::supplier_order4(),
            'taxes'  => [
                [
                    'taxable' => 'supplier_order4_item1',
                    'context' => 'supplier_order4',
                    'taxes'   => [Fixture::TAX_FR_NORMAL],
                ],
                [
                    'taxable' => 'supplier_order4_item2',
                    'context' => 'supplier_order4',
                    'taxes'   => [Fixture::TAX_FR_INTERMEDIATE],
                ],
            ],
            'result' => [
                'total'     => 2783.00,
                'tax'       => 360.31,
                'items'     => 2182.69,
                'forwarder' => 0.,
                'weight'    => 46.1,
                'units'     => [
                    'supplier_order4_item1' => [
                        'price'    => 55.32000,
                        'shipping' => 4.16486,
                    ],
                    'supplier_order4_item2' => [
                        'price'    => 49.69000,
                        'shipping' => 6.76790,
                    ],
                ],
            ],
        ];
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
