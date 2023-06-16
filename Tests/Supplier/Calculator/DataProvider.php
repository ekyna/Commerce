<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Supplier\Calculator;

use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use Generator;

/**
 * Class DataProvider
 * @package Ekyna\Component\Commerce\Tests\Supplier\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class DataProvider
{
    public static function provideData(): Generator
    {
        yield 'FR -> FR, discount, no forwarder' => [
            'order'  => Data::supplier_order1(),
            'result' => [
                'order' => [
                    'total'     => 1593.94,
                    'tax'       => 236.72,
                    'items'     => 1187.22,
                    'forwarder' => 0.,
                    'weight'    => 60.3,
                ],
                'item'  => [
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
            'result' => [
                'order' => [
                    'total'     => 3143.76,
                    'tax'       => 0.,
                    'items'     => 2843.76,
                    'forwarder' => 0.,
                    'weight'    => 79.2,
                ],
                'item'  => [
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
            'result' => [
                'order' => [
                    'total'     => 8934.14,
                    'tax'       => 0.,
                    'items'     => 7074.14,
                    'forwarder' => 1969.21,
                    'weight'    => 107.4,
                ],
                'item'  => [
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
            'result' => [
                'order' => [
                    'total'     => 2783.00,
                    'tax'       => 360.31,
                    'items'     => 2182.69,
                    'forwarder' => 0.,
                    'weight'    => 46.1,
                ],
                'item'  => [
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

        yield 'Missing weight' => [
            'order'  => [
                '_tax_resolver' => [],
                'supplier'      => [],
                'currency'      => Fixture::CURRENCY_EUR,
                'shipping_cost' => 320.,
                'items'         => [
                    [
                        '_reference' => 'missing_weight_item1',
                        'weight'     => 0.8,
                        'price'      => 62.57,
                        'quantity'   => 25.,
                        '_weighting' => [
                            'weight' => 0.04,
                            'price'  => 0.0348833967965479,
                        ],
                        // Total weight:  20.00  |  Weighting: 0,04 (1,0)
                        // Total price: 1564.25  |  Weighting: 0,0348833967965479 (0,8720849199136975)
                    ],
                    [
                        '_reference' => 'missing_weight_item2',
                        'weight'     => 0,
                        'price'      => 19.12,
                        'quantity'   => 12.,
                        '_weighting' => [
                            'weight' => 0.00,
                            'price'  => 0.0106595900071919,
                        ],
                        // Total weight:    0.00  |  Weighting: 0,0 (0,0)
                        // Total price:   229.44  |  Weighting: 0,0106595900071919 (0,1279150800863025)
                    ],
                    // Total price:   1793.69
                    // Total weight:    20.00
                    // Total quantity:  37
                ],
            ],
            'result' => [
                'order' => [
                    'total'     => 2113.69,
                    'tax'       => 0.00,
                    'items'     => 1793.69,
                    'forwarder' => 0.,
                    'weight'    => 20.00,
                ],
                'item'  => [
                    'missing_weight_item1' => [
                        'price'    => 62.57,
                        'shipping' => 12.80,
                    ],
                    'missing_weight_item2' => [
                        'price'    => 19.12,
                        'shipping' => 0.0,
                    ],
                ],
            ],
        ];

        yield 'Missing price' => [
            'order'  => [
                '_tax_resolver' => [],
                'supplier'      => [],
                'currency'      => Fixture::CURRENCY_EUR,
                'shipping_cost' => 320.,
                'items'         => [
                    [
                        '_reference' => 'missing_price_item1',
                        'weight'     => 0.8,
                        'price'      => 62.57,
                        'quantity'   => 25., //  Weighting: 0,027027027027027 (0,6756756756756757)
                        '_weighting' => [
                            'weight' => 0.04,
                            'price'  => 0.04,
                        ],
                        // Total weight:  20.00  |  Weighting: 0,04 (1,0)
                        // Total price: 1564.25  |  Weighting: 0.04 (1,0)
                    ],
                    [
                        '_reference' => 'missing_price_item2',
                        'weight'     => 0,
                        'price'      => 0,
                        'quantity'   => 12.,   //  Weighting: 0,027027027027027 (0,6756756756756757)
                        '_weighting' => [
                            'weight' => 0,
                            'price'  => 0,
                        ],
                        // Total weight:    0.00  |  Weighting: 0,0 (0,0)
                        // Total price:     0.00  |  Weighting: 0,0 (0,0)
                    ],
                    // Total price:   1564.25
                    // Total weight:    20.00
                    // Total quantity:  37
                ],
            ],
            'result' => [
                'order' => [
                    'total'     => 1884.25,
                    'tax'       => 0.00,
                    'items'     => 1564.25,
                    'forwarder' => 0.,
                    'weight'    => 20.00,
                ],
                'item'  => [
                    'missing_price_item1' => [
                        'price'    => 62.57,
                        'shipping' => 12.80,
                    ],
                    'missing_price_item2' => [
                        'price'    => 0.00,
                        'shipping' => 0.00,
                    ],
                ],
            ],
        ];

        yield 'Missing price, with discount' => [
            'order'  => [
                '_tax_resolver'  => [],
                'supplier'       => [],
                'currency'       => Fixture::CURRENCY_EUR,
                'shipping_cost'  => 320.,
                'discount_total' => 150.,
                'items'          => [
                    [
                        '_reference' => 'missing_price_item1',
                        'weight'     => 0.8,
                        'price'      => 62.57,
                        'quantity'   => 25.,    //  Weighting: 0,027027027027027 (0,6756756756756757)
                        '_weighting' => [
                            'weight' => 0.04,
                            'price'  => 0.04,
                        ],
                        // Total weight:  20.00  |  Weighting: 0,04 (1,0)
                        // Total price: 1564.25  |  Weighting: 0.04 (1,0)
                    ],
                    [
                        '_reference' => 'missing_price_item2',
                        'weight'     => 0,
                        'price'      => 0,
                        'quantity'   => 12.,   //  Weighting: 0,027027027027027 (0,6756756756756757)
                        '_weighting' => [
                            'weight' => 0,
                            'price'  => 0,
                        ],
                        // Total weight:   0.00  |  Weighting: 0,0 (0,0)
                        // Total price:    0.00  |  Weighting: 0,0 (0,0)
                    ],
                    // Total price:   1564.25
                    // Total weight:    20.00
                    // Total quantity:  37
                ],
            ],
            'result' => [
                'order' => [
                    'total'     => 1734.25,
                    'tax'       => 0.00,
                    'items'     => 1564.25,
                    'forwarder' => 0.,
                    'weight'    => 20.00,
                ],
                'item'  => [
                    'missing_price_item1' => [
                        'price'    => 56.57,
                        'shipping' => 12.8,
                    ],
                    'missing_price_item2' => [
                        'price'    => 0.00,
                        'shipping' => 0.00,
                    ],
                ],
            ],
        ];
    }
}
