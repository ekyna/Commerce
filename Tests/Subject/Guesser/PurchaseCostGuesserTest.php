<?php

namespace Ekyna\Component\Commerce\Tests\Subject\Guesser;

use Ekyna\Component\Commerce\Subject\Guesser\PurchaseCostGuesser;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;

/**
 * Class PurchaseCostGuesserTest
 * @package Ekyna\Component\Commerce\Tests\Subject\Guesser
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PurchaseCostGuesserTest extends TestCase
{
    /**
     * @var PurchaseCostGuesser
     */
    private $guesser;

    protected function setUp(): void
    {
        $this->guesser = new PurchaseCostGuesser(
            $this->getEntityManagerMock(),
            $this->getSupplierOrderItemRepositoryMock(),
            $this->getSupplierProductRepositoryMock(),
            $this->getCurrencyConverter(),
        );

        $unitRepository = $this->getStockUnitRepositoryMock();

        $this
            ->getEntityManagerMock()
            ->method('getRepository')
            ->willReturn($unitRepository);
    }

    protected function tearDown(): void
    {
        $this->guesser = null;
    }

    /**
     * @param float|null $expected
     * @param string     $currency
     * @param bool       $shipping
     * @param array      $units
     * @param array      $item
     * @param array      $products
     *
     * @dataProvider provide_guess
     */
    public function test_guess(
        float $expected = null,
        string $currency = null,
        bool $shipping = false,
        array $units = [],
        array $item = null,
        array $products = []
    ): void {
        $subject = Fixture::subject();

        $this
            ->getStockUnitRepositoryMock()
            ->method('findAssignableBySubject')
            ->with($subject)
            ->willReturn(array_map(function (array $data) {
                return Fixture::stockUnit($data);
            }, $units));

        $this
            ->getSupplierOrderItemRepositoryMock()
            ->method('findLatestOrderedBySubject')
            ->with($subject)
            ->willReturn($item ? Fixture::supplierOrderItem($item) : null);

        $this
            ->getSupplierProductRepositoryMock()
            ->method('findBySubject')
            ->with($subject)
            ->willReturn(array_map(function (array $data) {
                return Fixture::supplierProduct($data);
            }, $products));

        $this->assertSame($expected, $this->guesser->guess($subject, $currency, $shipping));
    }

    public function provide_guess(): \Generator
    {
        yield 'No data available' => [
            'expected' => null,
            'currency' => null,
            'shipping' => false,
            'units'    => [],
            'item'     => null,
            'products' => [],
        ];

        yield 'Stock units, EUR' => [
            'expected' => 60.75,
            'currency' => Fixture::CURRENCY_EUR,
            'shipping' => true,
            'units'    => [
                [
                    'item'           => [
                        'order' => [
                            'currency' => Fixture::CURRENCY_EUR,
                        ],
                    ],
                    'net_price'      => 49.50,
                    'shipping_price' => 2.53,
                ],
                [
                    'item'           => [
                        'order' => [
                            'currency' => Fixture::CURRENCY_USD,
                        ],
                    ],
                    'net_price'      => 64.89,
                    'shipping_price' => 4.59,
                ],
            ],
            'item'     => null,
            'products' => [],
        ];

        yield 'Stock units, USD' => [
            'expected' => 66.63,
            'currency' => Fixture::CURRENCY_USD,
            'shipping' => false,
            'units'    => [
                [
                    'item'      => [
                        'order' => [
                            'currency' => Fixture::CURRENCY_EUR,
                            // EUR/USD default exchange rate is 1.25
                        ],
                    ],
                    'net_price' => 49.50, // 61.875
                ],
                [
                    'item'      => [
                        'order' => [
                            'currency'      => Fixture::CURRENCY_USD,
                            'exchange_rate' => 1.1, // EUR/USD
                        ],
                    ],
                    'net_price' => 64.89, // 71.379 USD
                ],
            ],
            'item'     => null,
            'products' => [],
        ];

        yield 'Supplier order item, EUR 1' => [
            'expected' => 29.54,
            'currency' => Fixture::CURRENCY_EUR,
            'shipping' => false,
            'units'    => [],
            'item'     => [
                'order' => [/* EUR */],
                'price' => 29.54,
            ],
            'products' => [],
        ];

        yield 'Supplier order item, EUR 2' => [
            'expected' => 23.63,
            'currency' => Fixture::CURRENCY_EUR,
            'shipping' => false,
            'units'    => [],
            'item'     => [
                'order' => [
                    'currency'      => Fixture::CURRENCY_USD,
                    'exchange_rate' => 1.1, // EUR/USD (won't use)
                ],
                'price' => 29.54,
            ],
            'products' => [],
        ];

        yield 'Supplier order item, USD 1' => [
            'expected' => 58.47,
            'currency' => Fixture::CURRENCY_USD,
            'shipping' => false,
            'units'    => [],
            'item'     => [
                'order' => [
                    'currency' => Fixture::CURRENCY_EUR,
                    // EUR/USD default exchange rate is 1.25
                ],
                'price' => 46.78,
            ],
            'products' => [],
        ];

        yield 'Supplier order item, USD 2' => [
            'expected' => 46.78,
            'currency' => Fixture::CURRENCY_USD,
            'shipping' => false,
            'units'    => [],
            'item'     => [
                'order' => [
                    'currency' => Fixture::CURRENCY_USD,
                    // EUR/USD default exchange rate is 1.25
                ],
                'price' => 46.78,
            ],
            'products' => [],
        ];

        yield 'Supplier product, EUR 1' => [
            'expected' => 36.37,
            'currency' => Fixture::CURRENCY_EUR,
            'shipping' => false,
            'units'    => [],
            'item'     => null,
            'products' => [
                [
                    'supplier' => [/* EUR */],
                    'price'    => 38.16,
                ],
                [
                    'supplier' => [/* EUR */],
                    'price'    => 34.58,
                ],
            ],
        ];

        yield 'Supplier product, EUR 2' => [
            'expected' => 90.46,
            'currency' => Fixture::CURRENCY_EUR,
            'shipping' => false,
            'units'    => [],
            'item'     => null,
            'products' => [
                [
                    'supplier' => [/* EUR */],
                    'price'    => 89.64,
                ],
                [
                    'supplier' => [
                        'currency' => Fixture::CURRENCY_GBP,
                    ],
                    'price'    => 82.15, // 91.27777 EUR
                ],
            ],
        ];

        yield 'Supplier product, USD 1' => [
            'expected' => 96.16,
            'currency' => Fixture::CURRENCY_USD,
            'shipping' => false,
            'units'    => [],
            'item'     => null,
            'products' => [
                [
                    'supplier' => [/* EUR */],
                    'price'    => 82.15, // 112.05 USD
                ],
                [
                    'supplier' => [
                        'currency' => Fixture::CURRENCY_USD,
                    ],
                    'price'    => 89.64,
                ],
            ],
        ];

        yield 'Supplier product, USD 2' => [
            'expected' => 113.07,
            'currency' => Fixture::CURRENCY_USD,
            'shipping' => false,
            'units'    => [],
            'item'     => null,
            'products' => [
                [
                    'supplier' => [/* EUR */],
                    'price'    => 89.64, // 112.05
                ],
                [
                    'supplier' => [
                        'currency' => Fixture::CURRENCY_GBP,
                    ],
                    'price'    => 82.15, // ‭114,09723135‬ USD
                ],
            ],
        ];
    }
}
