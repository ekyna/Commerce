<?php
/** @noinspection PhpUnused */

/** @noinspection PhpMethodNamingConventionInspection */

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests;

use Ekyna\Component\Commerce\Common\Model\AdjustmentTypes;

/**
 * Class Data
 * @package Ekyna\Component\Commerce\Tests
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Data
{
    public static function subject1(): array
    {
        return [
            '_type'       => 'subject',
            '_reference'  => 'subject1',
            'designation' => 'Subject 1',
            'reference'   => 'SU-1',
            'tax_group'   => Fixture::TAX_GROUP_NORMAL,
            'price'       => 24.90,
            'weight'      => 0.8,
        ];
    }

    public static function subject2(): array
    {
        return [
            '_type'       => 'subject',
            '_reference'  => 'subject2',
            'designation' => 'Subject 2',
            'reference'   => 'SU-2',
            'tax_group'   => Fixture::TAX_GROUP_INTERMEDIATE,
            'price'       => 69.50,
            'weight'      => 1.3,
        ];
    }

    public static function subject3(): array
    {
        return [
            '_type'       => 'subject',
            '_reference'  => 'subject3',
            'designation' => 'Subject 3',
            'reference'   => 'SU-3',
            'tax_group'   => Fixture::TAX_GROUP_NORMAL,
            'price'       => 65.40,
            'weight'      => 0.5,
        ];
    }

    public static function subject4(): array
    {
        return [
            '_type'       => 'subject',
            '_reference'  => 'subject4',
            'designation' => 'Subject 4',
            'reference'   => 'SU-4',
            'tax_group'   => Fixture::TAX_GROUP_INTERMEDIATE,
            'price'       => 45.67,
            'weight'      => 1.1,
        ];
    }

    public static function subject5(): array
    {
        return [
            '_type'       => 'subject',
            '_reference'  => 'subject5',
            'designation' => 'Subject 5',
            'reference'   => 'SU-5',
            'tax_group'   => Fixture::TAX_GROUP_NORMAL,
            'price'       => 82.60,
            'weight'      => 0.8,
        ];
    }

    public static function subject6(): array
    {
        return [
            '_type'       => 'subject',
            '_reference'  => 'subject6',
            'designation' => 'Subject 6',
            'reference'   => 'SU-6',
            'tax_group'   => Fixture::TAX_GROUP_INTERMEDIATE,
            'price'       => 78.20,
            'weight'      => 1.4,
        ];
    }

    public static function stock_unit9(): array
    {
        return [
            '_type'          => 'stockUnit',
            '_reference'     => 'stock_unit9',
            'net_price'      => 0.,
            'shipping_price' => 0.,
            'ordered'        => 0.,
            'sold'           => 3.,
            'subject'        => 'subject2',
        ];
    }

    public static function order1(): array
    {
        return [
            '_reference'      => 'order1',
            '_type'           => 'order',
            '_amount'         => [
                'shipment' => [
                    'unit'     => 15.26,
                    'gross'    => 15.26,
                    'discount' => 0.0,
                    'base'     => 15.26,
                    'tax'      => 3.05,
                    'total'    => 18.31,
                ],
                'gross'    => [
                    'unit'     => 1029.93,
                    'gross'    => 1029.93,
                    'discount' => 93.61,
                    'base'     => 936.32,
                    'tax'      => 66.19,
                    'total'    => 1002.51,
                ],
                'final'    => [
                    'unit'     => 936.32,
                    'gross'    => 936.32,
                    'discount' => 112.36,
                    'base'     => 839.22,
                    'tax'      => 61.30,
                    'total'    => 900.52,
                ],
            ],
            '_margin'         => [
                'revenue' => [
                    'product'  => 823.96,
                    'shipment' => 15.26,
                ],
                'cost'    => [
                    'product'  => 500.93,
                    'supply'   => 72.59,
                    'shipment' => 8.13,
                ],
            ],
            '_cost'           => [
                'shipment' => 8.13,
            ],
            'items'           => [
                [
                    '_reference' => 'order1_item1', // id: 1
                    'quantity'   => 3,
                    'price'      => 32.59,
                    'discounts'  => [7],
                    'taxes'      => [20],
                    'included' => [
                        [
                            'designation' => 'Ecotax',
                            'amount'      => 5,
                        ],
                    ],
                    // Calculation results
                    '_amount'    => [
                        'unit'     => 32.59,
                        'gross'    => 97.77,
                        'discount' => 6.84,
                        'base'     => 90.93,
                        'tax'      => 18.19,
                        'total'    => 109.12,
                    ],
                    '_margin'    => [
                        'revenue' => [
                            'product' => 90.93,
                        ],
                        'cost'    => [
                            'product' => 61.23,
                            'supply'  => 3.45,
                        ],
                    ],
                    '_cost'      => [
                        'product' => 61.23,
                        'supply'  => 3.45,
                    ],
                    '_weight'    => 0,
                    // Default                   // Revenue (invoiced: 3)
                    // Gross:     97.77          // Gross:     97.77
                    // Discount:   6.84          // Discount:   6.84
                    // Base:      90.93          // Base:      90.93
                    // Tax:       18.19          // Tax:       18.19
                    // Total:    109.12          // Total:    109.12
                ],
                [
                    '_reference' => 'order1_item2', // id: 2
                    'quantity'   => 4, // (invoiced: 4, credit: 1)
                    'price'      => 0,
                    'compound'   => true,
                    'children'   => [
                        [
                            '_reference' => 'order1_item2_1', // id: 3
                            'quantity'   => 5, // 20
                            'price'      => 12.34,
                            'discounts'  => [5],
                            'taxes'      => [5.5],
                            // Calculation results
                            '_amount'    => [
                                'unit'     => 12.34,
                                'gross'    => 246.80,
                                'discount' => 12.34,
                                'base'     => 234.46,
                                'tax'      => 12.90,
                                'total'    => 247.36,
                            ],
                            '_margin'    => [
                                'revenue' => [
                                    'product' => 234.46,
                                ],
                                'cost'    => [
                                    'product' => 165.12,
                                    'supply'  => 23.61,
                                ],
                            ],
                            '_cost'      => [
                                'product' => 165.12,
                                'supply'  => 23.61,
                            ],
                            // Default                   // Revenue (invoiced: 20, credit: 5)
                            // Gross:    246.80          // Gross:    185.10
                            // Discount:  12.34          // Discount:   9.25
                            // Base:     234.46          // Base:     175.85
                            // Tax:       12.90          // Tax:        9.67
                            // Total:    247.36          // Total:    185.52
                        ],
                        [
                            '_reference' => 'order1_item2_2', // id: 4
                            'quantity'   => 2, // 8
                            'price'      => 47.99,
                            'discounts'  => [10],
                            'taxes'      => [5.5],
                            'compound'   => true,
                            'children'   => [
                                [
                                    '_reference' => 'order1_item2_2_1', // id: 5
                                    'quantity'   => 2, // 16
                                    'price'      => 3.99,
                                    'private'    => true,
                                    // Calculation results
                                    '_amount'    => [
                                        'unit'    => 3.99,
                                        '_single' => [
                                            'unit'     => 3.99,
                                            'gross'    => 63.84,
                                            'discount' => 6.38, // 10%
                                            'base'     => 57.46,
                                            'tax'      => 3.16, // 5.5%
                                            'total'    => 60.62,
                                        ],
                                    ],
                                    '_margin'    => [
                                        'revenue' => [],
                                        'cost'    => [],
                                        '_single' => [
                                            'revenue' => [
                                                'product' => 57.46,
                                            ],
                                            'cost'    => [
                                                'product' => 34.56,
                                                'supply'  => 1.23,
                                            ],
                                        ],
                                    ],
                                    '_cost'      => [
                                        '_single' => [
                                            'product' => 34.56,
                                            'supply'  => 1.23,
                                        ],
                                    ],
                                ],
                                [
                                    '_reference' => 'order1_item2_2_2', // id: 6
                                    'quantity'   => 3, // 24
                                    'price'      => 4.99,
                                    'private'    => true,
                                    // Calculation results
                                    '_amount'    => [
                                        'unit'    => 4.99,
                                        '_single' => [
                                            'unit'     => 4.99,
                                            'gross'    => 119.76,
                                            'discount' => 11.98, // 10%
                                            'base'     => 107.78,
                                            'tax'      => 5.93,  // 5.5%
                                            'total'    => 113.71,
                                        ],
                                    ],
                                    '_margin'    => [
                                        'revenue' => [],
                                        'cost'    => [],
                                        '_single' => [
                                            'revenue' => [
                                                'product' => 107.78,
                                            ],
                                            'cost'    => [
                                                'product' => 65.43,
                                                'supply'  => 13.24,
                                            ],
                                        ],
                                    ],
                                    '_cost'      => [
                                        '_single' => [
                                            'product' => 65.43,
                                            'supply'  => 13.24,
                                        ],
                                    ],
                                ],
                            ],
                            // Calculation results
                            '_amount'    => [
                                'unit'     => 70.94,
                                'gross'    => 567.52,
                                'discount' => 56.75, // 10%
                                'base'     => 510.77,
                                'tax'      => 28.09, // 5.5%
                                'total'    => 538.86,
                                '_single'  => [
                                    'unit'     => 47.99,
                                    'gross'    => 383.92,
                                    'discount' => 38.39, // 10%
                                    'base'     => 345.53,
                                    'tax'      => 19.00, // 5.5%
                                    'total'    => 364.53,
                                ],
                            ],
                            '_margin'    => [
                                'revenue' => [
                                    'product' => 510.77,
                                ],
                                'cost'    => [
                                    'product' => 228.24,
                                    'supply'  => 41.01,
                                ],
                                '_single' => [
                                    'revenue' => [
                                        'product' => 345.53,
                                    ],
                                    'cost'    => [
                                        'product' => 128.25,
                                        'supply'  => 26.54,
                                    ],
                                ],
                            ],
                            '_cost'      => [
                                'product' => 228.24,
                                'supply'  => 41.01,
                                '_single' => [
                                    'product' => 128.25,
                                    'supply'  => 26.54,
                                ],
                            ],
                            // Default                   // Revenue (invoiced: 8, credited: 2)
                            // Unit:      70.94          // Unit:      70.94
                            // Gross:    567.52          // Gross:    425.64
                            // Discount:  56.75          // Discount:  42.56
                            // Base:     510.77          // Base:     383.08
                            // Tax:       28.09          // Tax:       21.07
                            // Total:    538.86          // Total:    404.15
                        ],
                    ],
                    // Calculation results
                    '_amount'    => [],
                    '_margin'    => [],
                    '_cost'      => [],
                ],
                [
                    '_reference' => 'order1_item3', // id: 7
                    'quantity'   => 6,
                    'price'      => 11.36,
                    'discounts'  => [15],
                    'taxes'      => [7],
                    'children'   => [
                        [
                            '_reference' => 'order1_item3_1', // id: 8
                            'quantity'   => 2, // 12
                            'price'      => 4.14,
                            'private'    => true,
                            'included' => [
                                [
                                    'designation' => 'Ecotax',
                                    'amount'      => 1,
                                ],
                            ],
                            '_amount'    => [
                                'unit'    => 4.14,
                                '_single' => [
                                    'unit'     => 4.14,
                                    'gross'    => 49.68,
                                    'discount' => 7.45, // 15%
                                    'base'     => 42.23,
                                    'tax'      => 2.96, // 7%
                                    'total'    => 45.19,
                                ],
                            ],
                            '_margin'    => [
                                '_single' => [
                                    'revenue' => [
                                        'product' => 42.23,
                                    ],
                                    'cost'    => [
                                        'product' => 25.24,
                                        'supply'  => 4.52,
                                    ],
                                ],
                            ],
                            '_cost'      => [
                                '_single' => [
                                    'product' => 25.24,
                                    'supply'  => 4.52,
                                ],
                            ],
                        ],
                    ],
                    // Calculation results
                    '_amount'    => [
                        'unit'     => 19.64,
                        'gross'    => 117.84,
                        'discount' => 17.68, // 15%
                        'base'     => 100.16,
                        'tax'      => 7.01, // 7%
                        'total'    => 107.17,
                        '_single'  => [
                            'unit'     => 11.36,
                            'gross'    => 22.72,
                            'discount' => 3.4, // 15%
                            'base'     => 19.32,
                            'tax'      => 1.35, // 7%
                            'total'    => 20.67,
                        ],
                    ],
                    '_margin'    => [
                        'revenue' => [
                            'product' => 100.16,
                        ],
                        'cost'    => [
                            'product' => 46.34,
                            'supply'  => 4.52,
                        ],
                        '_single' => [
                            'revenue' => [
                                'product' => 19.32,
                            ],
                            'cost'    => [
                                'product' => 21.1,
                                'supply'  => 0,
                            ],
                        ],
                    ],
                    '_cost'      => [
                        'product' => 46.34,
                        'supply'  => 4.52,
                        '_single' => [
                            'product' => 21.1,
                            'supply'  => 0,
                        ],
                    ],
                    // Default                   // Revenue (invoiced: 6, refunded: 4)
                    // Unit:      19.64          // Unit:      19.64
                    // Gross:    117.84          // Gross:     39.28
                    // Discount:  17.68          // Discount:   5.89
                    // Base:     100.16          // Base:      33.39
                    // Tax:        7.01          // Tax:        2.34
                    // Total:    107.17          // Total:     35.73
                ],
                // Default                   // Revenue (invoiced: 6, refunded: 4)
                // Gross:   1029.93          // Gross:     39.28
                // Discount:  93.61          // Discount:   5.89
                // Base:     936.32          // Base:      33.39
                // Tax:       66.19          // Tax:        2.34
                // Total:   1002.51          // Total:     35.73
            ],
            'discounts'       => [
                [
                    '_reference' => 'order1_discount1',
                    'type'       => AdjustmentTypes::TYPE_DISCOUNT,
                    'amount'     => 12,
                    '_amount'    => [
                        'unit'     => 112.36,
                        'gross'    => 112.36,
                        'discount' => 0.00,
                        'base'     => 112.36,
                        'tax'      => 7.94,
                        'total'    => 120.30,
                    ],
                ],
            ],
            'taxes'           => [20],
            'shipment_amount' => 15.26,
            'grand_total'     => 900.52,
            //'invoice_total'   => 900.52, // So that $order->isFullyInvoiced() return true
            'invoices'        => [
                [
                    '_reference' => 'order1_invoice1',
                    'lines'      => [
                        [
                            'item'     => ['_reference' => 'order1_item2_1'],
                            'quantity' => 20,
                            'base'     => 234.46,
                        ],
                        [
                            'item'     => ['_reference' => 'order1_item2_2'],
                            'quantity' => 8,
                            'base'     => 510.77,
                        ],
                        [
                            'item'     => ['_reference' => 'order1_item2_2_1'],
                            'quantity' => 16,
                            'base'     => 0.0,
                        ],
                        [
                            'item'     => ['_reference' => 'order1_item2_2_2'],
                            'quantity' => 24,
                            'base'     => 0.0,
                        ],
                        [
                            'item'     => ['_reference' => 'order1_item3'],
                            'quantity' => 3,
                            'base'     => 50.08,
                        ],
                        [
                            'item'     => ['_reference' => 'order1_item3_1'],
                            'quantity' => 6,
                            'base'     => 0.0,
                        ],
                    ],
                ],
                [
                    '_reference' => 'order1_invoice2',
                    'lines'      => [
                        [
                            'item'     => ['_reference' => 'order1_item1'],
                            'quantity' => 3,
                            'base'     => 90.93,
                        ],
                        [
                            'item'     => ['_reference' => 'order1_item3'],
                            'quantity' => 3,
                            'base'     => 50.08,
                        ],
                        [
                            'item'     => ['_reference' => 'order1_item3_1'],
                            'quantity' => 6,
                            'base'     => 0.0,
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function order2(): array
    {
        return [
            '_reference' => 'order2',
            '_type'      => 'order',
            'items'      => [
                [
                    '_reference' => 'order2_item1',
                    'quantity'   => 3,
                    'price'      => 249.99167,
                    'taxes'      => [20],
                ],
                [
                    '_reference' => 'order2_item2',
                    'quantity'   => 3,
                    'price'      => 59.48333,
                    'taxes'      => [20],
                ],
                [
                    '_reference' => 'order2_item3',
                    'quantity'   => 3,
                    'price'      => 42.48333,
                    'taxes'      => [20],
                ],
                [
                    '_reference' => 'order2_item4',
                    'quantity'   => 3,
                    'price'      => 12.74167,
                    'taxes'      => [20],
                ],
                [
                    '_reference' => 'order2_item5',
                    'quantity'   => 3,
                    'price'      => 15.29167,
                    'taxes'      => [20],
                ],
            ],
        ];
    }

    public static function order3(): array
    {
        return [
            '_reference'      => 'order3',
            '_type'           => 'order',
            '_dependencies'   => [
                'supplier_order1',
                'supplier_order2',
                'supplier_order3',
                'supplier_order4',
            ],
            '_amount'         => [
                'shipment' => [
                    'unit'     => 12.34,
                    'gross'    => 12.34,
                    'discount' => 0.0,
                    'base'     => 12.34,
                    'tax'      => 2.47,
                    'total'    => 14.81,
                ],
                'gross'    => [
                    'unit'     => 2493.80,
                    'gross'    => 2493.80,
                    'discount' => 203.90,
                    'base'     => 2289.90,
                    'tax'      => 365.54,
                    'total'    => 2655.44,
                ],
                'final'    => [
                    'unit'     => 2289.90,
                    'gross'    => 2289.90,
                    'discount' => 0,
                    'base'     => 2302.24,
                    'tax'      => 368.01,
                    'total'    => 2670.25,
                ],
            ],
            '_margin'         => [
                'revenue' => [
                    'product'  => 2289.90,
                    'shipment' => 12.34,
                ],
                'cost'    => [
                    'product'  => 1589.6,
                    'supply'   => 307.13,
                    'shipment' => 47.00,
                    'average'  => true,
                ],
            ],
            '_cost'           => [
                'shipment' => 47.0,
            ],
            'invoice_address' => [
                '_reference' => 'order3_invoiceAddress',
                'country'    => Fixture::COUNTRY_FR,
            ],
            'items'           => [
                [
                    //'_id'         => 1,
                    '_reference'  => 'order3_item1',
                    'subject'     => 'subject6', // (price: 78.20, weight: 1.4)
                    'quantity'    => 4,
                    'taxes'       => [10],
                    'assignments' => [
                        [
                            'unit' => 'stock_unit6', // (price: 60.30, shipping: 26.35)
                            'sold' => 2.,
                        ],
                        [
                            'unit' => 'stock_unit8', // (price: 49.69, shipping: 6.77)
                            'sold' => 2.,
                        ],
                    ],
                    // Calculation results
                    '_amount'     => [
                        'unit'     => 78.20,
                        'gross'    => 312.80,
                        'discount' => 0,
                        'base'     => 312.80,
                        'tax'      => 31.28,
                        'total'    => 344.08,
                    ],
                    '_margin'     => [
                        // 92.82 (29.67%) | 26.58 (8,50%)
                        'revenue' => [
                            'product' => 312.80,
                        ],
                        'cost'    => [
                            'product' => 219.98,
                            'supply'  => 66.24,
                        ],
                    ],
                    '_cost'       => [
                        //'product' => 54.995,
                        //'supply'  => 16.56,
                        'product' => 219.98,
                        'supply'  => 66.24,
                    ],
                    '_weight'     => 5.6,
                ],
                [
                    //'_id'         => 2,
                    '_reference'  => 'order3_item2',
                    'subject'     => 'subject2', // (price: 69.50, weight: 1.3)
                    'quantity'    => 10,
                    'discounts'   => [12],
                    'taxes'       => [10],
                    'assignments' => [
                        [
                            'unit' => 'stock_unit2', // (price: 49.61, shipping: 6.90)
                            'sold' => 7., // 3 left
                        ],
                        [
                            'unit' => [
                                '_reference' => 'stock_unit9', // (price: 52.31)
                            ],
                            'sold' => 3.,
                        ],
                    ],
                    // Calculation results
                    '_amount'     => [
                        'unit'     => 69.50,
                        'gross'    => 695.00,
                        'discount' => 83.40,
                        'base'     => 611.60,
                        'tax'      => 61.16,
                        'total'    => 672.76,
                    ],
                    '_margin'     => [
                        // 107.40 (17.56%) | 45.96 (7.51%)
                        'revenue' => [
                            'product' => 611.60,
                        ],
                        'cost'    => [
                            'product' => 504.20,
                            'supply'  => 48.3,
                            'average' => true,
                        ],
                    ],
                    '_cost'       => [
                        //'product' => 50.42,
                        //'supply'  => 4.83,
                        //'average' => true,
                        'product' => 504.20,
                        'supply'  => 48.3,
                        'average' => true,
                    ],
                    '_weight'     => 13.0,
                ],
                [
                    //'_id'         => 3,
                    '_reference' => 'order3_item3',
                    // TODO 'subject'     => 'subject7',
                    'quantity'   => 2,
                    'price'      => 0,
                    'weight'     => 0,
                    'compound'   => true,
                    // Calculation results
                    '_amount'    => [],
                    '_margin'    => [],
                    '_cost'      => [],
                    '_weight'    => 0,
                    'children'   => [
                        [
                            //'_id'         => 4,
                            '_reference'  => 'order3_item3_1',
                            'subject'     => 'subject5', // (price: 82.60, weight: 0.8)
                            'quantity'    => 5, // 10
                            'discounts'   => [5],
                            'taxes'       => [20],
                            'assignments' => [
                                [
                                    'unit' => 'stock_unit5', // (price: 51.98, shipping: 15.05)
                                    'sold' => 7.,
                                ],
                                [
                                    'unit' => 'stock_unit7', // (price: 55.32, shipping: 4.16)
                                    'sold' => 3.,
                                ],
                            ],
                            // Calculation results
                            '_amount'     => [
                                'unit'     => 82.60,
                                'gross'    => 826.00,
                                'discount' => 41.30,
                                'base'     => 784.70,
                                'tax'      => 156.94,
                                'total'    => 941.64,
                            ],
                            '_margin'     => [
                                // 254.88 (32.48%) | 137.05 (17.47%)
                                'revenue' => [
                                    'product' => 784.70,
                                ],
                                'cost'    => [
                                    'product' => 529.82,
                                    'supply'  => 117.83,
                                ],
                            ],
                            '_cost'       => [
                                //'product' => 52.982,
                                //'supply'  => 11.783,
                                'product' => 529.82,
                                'supply'  => 117.83,
                            ],
                            '_weight'     => 8.0,
                        ],
                        [
                            //'_id'         => 5,
                            '_reference'  => 'order3_item3_2',
                            'subject'     => 'subject3', // (price: 65.40, weight: 0.5)
                            'quantity'    => 2, // 4
                            'discounts'   => [12],
                            'taxes'       => [20],
                            'assignments' => [
                                [
                                    'unit' => 'stock_unit3', // (price: 40.78, shipping: 1.69)
                                    'sold' => 4.,
                                ],
                            ],
                            'children'    => [
                                [
                                    //'_id'         => 6,
                                    '_reference'  => 'order3_item3_2_1',
                                    'subject'     => 'subject1', // (price: 24.90, weight: 0.8)
                                    'quantity'    => 4, // 16
                                    'private'     => true,
                                    'assignments' => [
                                        [
                                            'unit' => 'stock_unit1', // (price: 10.78, shipping: 4.25)
                                            'sold' => 16.,
                                        ],
                                    ],
                                    // Calculation results
                                    '_amount'     => [
                                        'unit'    => 24.90,
                                        '_single' => [
                                            'unit'     => 24.90,
                                            'gross'    => 398.4,
                                            'discount' => 47.81, // 12%
                                            'base'     => 350.59,
                                            'tax'      => 70.12, // 20%
                                            'total'    => 420.71,
                                        ],
                                    ],
                                    '_margin'     => [
                                        'revenue' => [],
                                        'cost'    => [],
                                        '_single' => [
                                            'revenue' => [
                                                'product' => 350.59,
                                            ],
                                            'cost'    => [
                                                'product' => 172.48,
                                                'supply'  => 68.0,
                                            ],
                                        ],
                                    ],
                                    '_cost'       => [
                                        //'product' => 10.78,
                                        //'supply'  => 4.25,
                                        '_single' => [
                                            'product' => 172.48,
                                            'supply'  => 68.0,
                                        ],
                                    ],
                                    '_weight'     => 12.8,
                                ],
                            ],
                            // Calculation results
                            '_amount'     => [
                                'unit'     => 165.00,
                                'gross'    => 660.00,
                                'discount' => 79.20, // 12%
                                'base'     => 580.80,
                                'tax'      => 116.16, // 20%
                                'total'    => 696.96,
                                '_single'  => [
                                    'unit'     => 65.40,
                                    'gross'    => 261.6,
                                    'discount' => 31.39,
                                    'base'     => 230.21,
                                    'tax'      => 46.04,
                                    'total'    => 276.25,
                                ],
                            ],
                            '_margin'     => [
                                // 245.20 (42.22%) | 170.44 (29.35%)
                                'revenue' => [
                                    'product' => 580.8,
                                ],
                                'cost'    => [
                                    'product' => 335.6,
                                    'supply'  => 74.76,
                                ],
                                '_single' => [
                                    'revenue' => [
                                        'product' => 230.21,
                                    ],
                                    'cost'    => [
                                        'product' => 163.12,
                                        'supply'  => 6.76,
                                    ],
                                ],
                            ],
                            '_cost'       => [
                                //'product' => 40.78,
                                //'supply'  => 1.69,
                                'product' => 335.6,
                                'supply'  => 74.76,
                                '_single' => [
                                    'product' => 163.12,
                                    'supply'  => 6.76,
                                ],
                            ],
                            '_weight'     => 2.0,
                        ],
                    ],
                    // Weight:    41.4
                    // Gross:    1486.00
                    // Discount:  120.50
                    // Base:     1365.50
                    // Tax:       273.10
                    // Total:    1638.60
                    // Margin:    500.08 (36.62%) | 307.49 (22.52%)
                ],
                // Weight:     60.0
                // Gross:    2493.80
                // Discount:  203.90
                // Base:     2289.90
                // Tax:       365.54
                // Total:    2655.44
            ],
            'taxes'           => [20],
            'shipment_amount' => 12.34,
            'weight_total'    => 60.,
            'shipments'       => [
                [
                    '_reference' => 'order3_shipment1',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 45., // Cost 35.00
                ],
                [
                    '_reference' => 'order3_shipment2',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 15., // Cost 12.00
                ],
            ],
        ];
    }

    public static function order4(): array
    {
        return [
            '_reference' => 'order4',
            '_type'      => 'order',
            'items'      => [
                [
                    '_reference' => 'order4_item1',
                    'compound'   => true,
                    'children'   => [
                        [
                            '_reference' => 'order4_item1_1',
                            'private'    => true,
                            'compound'   => true,
                            'children'   => [
                                [
                                    '_reference' => 'order4_item1_1_1',
                                    'private'    => true,
                                ],
                                [
                                    '_reference' => 'order4_item1_1_2',
                                    'private'    => true,
                                ],
                            ],
                        ],
                        [
                            '_reference' => 'order4_item1_2',
                            'private'    => true,
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function supplier_order1(): array
    {
        return [
            '_reference'     => 'supplier_order1',
            '_type'          => 'supplierOrder',
            '_tax_resolver'  => [
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
            'supplier'       => [
                'tax' => Fixture::TAX_FR_NORMAL,
            ],
            'currency'       => Fixture::CURRENCY_EUR,
            'shipping_cost'  => 320.,
            'discount_total' => 150.,
            'items'          => [
                [
                    '_reference' => 'supplier_order1_item1',
                    '_weighting' => [
                        'default'  => 0.013266998341625,
                        'weight'   => 0.013266998341625,
                        'price'    => 0.010394029750173,
                        'quantity' => 0.014084507042254,
                    ],
                    'weight'     => 0.8,
                    'price'      => 12.34,
                    'quantity'   => 64.,
                    'unit'       => [
                        '_reference'     => 'stock_unit1',
                        'net_price'      => 10.78,
                        'shipping_price' => 4.25,
                        'ordered'        => 3.,
                        'sold'           => 16.,
                        'subject'        => 'subject1',
                    ],
                    // Total weight:  51.2 | Weighting: 0,013266998341625 (0,849087893864013)
                    // Total price: 789.76 | Weighting: 0,010394029750173 (0,665217904011051)
                    // Discount:    99,78268560165765
                    // Base:        689,9773143983424
                    // Tax:         138.00
                ],
                [
                    '_reference' => 'supplier_order1_item2',
                    '_weighting' => [
                        'default'  => 0.021558872305141,
                        'weight'   => 0.021558872305141,
                        'price'    => 0.047826013712707,
                        'quantity' => 0.014084507042254,
                    ],
                    'weight'     => 1.3,
                    'price'      => 56.78,
                    'quantity'   => 7.,
                    'unit'       => [
                        '_reference'     => 'stock_unit2',
                        'net_price'      => 49.61,
                        'shipping_price' => 6.90,
                        'ordered'        => 7.,
                        'sold'           => 7.,
                        'subject'        => 'subject2',
                    ],
                    // Total weight:  9.10 | Weighting: 0,021558872305141 (0,1509121061359867)
                    // Total price: 397.46 | Weighting: 0,047826013712707 (0,334782095988949)
                    // Discount:     50,21731439834235
                    // Base:        347,2426856016577  ()
                    // Tax:          34,72
                ],
                // Total price:  1187.22
                // Total weight:   60.3
                // Total quantity: 71
            ],
        ];
    }

    public static function supplier_order2(): array
    {
        return [
            '_reference'    => 'supplier_order2',
            '_type'         => 'supplierOrder',
            '_tax_resolver' => [
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
            'supplier'      => [],
            'currency'      => Fixture::CURRENCY_USD,
            'exchange_rate' => 1.12,
            'exchange_date' => 'now',
            'shipping_cost' => 300.,
            'items'         => [
                [
                    '_reference' => 'supplier_order2_item1',
                    '_weighting' => [
                        'default'  => 0.006313131313131,
                        'weight'   => 0.006313131313131,
                        'price'    => 0.016059723746026,
                        'quantity' => 0.011111111111111,
                    ],
                    'weight'     => 0.5,
                    'price'      => 45.67,
                    'quantity'   => 33.,
                    'unit'       => [
                        '_reference'     => 'stock_unit3',
                        'net_price'      => 40.78,
                        'shipping_price' => 1.69,
                        'ordered'        => 33.,
                        'sold'           => 4.,
                        'subject'        => 'subject3',
                    ],
                    // Total weight:   16.50  |  Weighting: 0,0063131313131313 (0,2083333333333333)
                    // Total price : 1507.11  |  Weighting: 0,0160597237460264 (0,5299708836188708)
                ],
                [
                    '_reference' => 'supplier_order2_item2',
                    '_weighting' => [
                        'default'  => 0.013888888888889,
                        'weight'   => 0.013888888888889,
                        'price'    => 0.008246124848792,
                        'quantity' => 0.011111111111111,
                    ],
                    'weight'     => 1.1,
                    'price'      => 23.45,
                    'quantity'   => 57.,
                    'unit'       => [
                        '_reference'     => 'stock_unit4',
                        'net_price'      => 20.94,
                        'shipping_price' => 3.72,
                        'ordered'        => 57.,
                        'sold'           => 0.,
                        'subject'        => 'subject4',
                    ],
                    // Total weight:   62.70  |  Weighting: 0,0138888888888889 (0,7916666666666667)
                    // Total price:  1336.65  |  Weighting: 0,0082461248487917 (0,4700291163811292)
                ],
                // Total price:   2843.76
                // Total weight:    79.2
                // Total quantity:  90
            ],
        ];
    }

    public static function supplier_order3(): array
    {
        return [
            '_reference'    => 'supplier_order3',
            '_type'         => 'supplierOrder',
            '_tax_resolver' => [
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
            'supplier'      => [],
            'carrier'       => ['tax' => Fixture::TAX_FR_NORMAL],
            'currency'      => Fixture::CURRENCY_USD,
            'shipping_cost' => 1860.,
            'customs_tax'   => 480.,
            'customs_vat'   => 1426.45,
            'forwarder_fee' => 52.3,
            'items'         => [
                [
                    '_reference' => 'supplier_order3_item1',
                    '_weighting' => [
                        'default'  => 0.007448789571695,
                        'weight'   => 0.007448789571695,
                        'price'    => 0.009184155247140,
                        'quantity' => 0.009803921568627,
                    ],
                    'weight'     => 0.8,
                    'price'      => 64.97,
                    'quantity'   => 59.,
                    'unit'       => [
                        '_reference'     => 'stock_unit5',
                        'net_price'      => 51.98,
                        'shipping_price' => 15.05,
                        'ordered'        => 59.,
                        'sold'           => 7.,
                        'subject'        => 'subject5',
                    ],
                    // Total weight:   47.20  |  Weighting: 0,0074487895716946 (0,4394785847299814)
                    // Total price:  3833.23  |  Weighting: 0,0091841552471396 (0,5418651595812353)
                ],
                [
                    '_reference' => 'supplier_order3_item2',
                    '_weighting' => [
                        'default'  => 0.013035381750466,
                        'weight'   => 0.013035381750466,
                        'price'    => 0.010654298614390,
                        'quantity' => 0.009803921568627,
                    ],
                    'weight'     => 1.4,
                    'price'      => 75.37,
                    'quantity'   => 43.,
                    'unit'       => [
                        '_reference'     => 'stock_unit6',
                        'net_price'      => 60.3,
                        'shipping_price' => 26.35,
                        'ordered'        => 43.,
                        'sold'           => 2.,
                        'subject'        => 'subject6',
                    ],
                    // Total weight:   60.20  |  Weighting: 0,0130353817504655 (0,5605214152700186)
                    // Total price:  3240.91  |  Weighting: 0,0106542986143899 (0,4581348404187647)
                ],
                // Total price:   7074.14
                // Total weight:   107.4
                // Total quantity: 102
            ],
        ];
    }

    public static function supplier_order4(): array
    {
        return [
            '_reference'    => 'supplier_order4',
            '_type'         => 'supplierOrder',
            '_tax_resolver' => [
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
            'supplier'      => ['tax' => Fixture::TAX_FR_NORMAL],
            'currency'      => Fixture::CURRENCY_EUR,
            'shipping_cost' => 240.,
            'items'         => [
                [
                    '_reference' => 'supplier_order4_item1',
                    '_weighting' => [
                        'default'  => 0.0173535791757050,
                        'weight'   => 0.0173535791757050,
                        'price'    => 0.0253448726113191,
                        'quantity' => 0.0238095238095238,
                    ],
                    'weight'     => 0.8,
                    'price'      => 55.32,
                    'quantity'   => 17.,
                    'unit'       => [
                        '_reference'     => 'stock_unit7',
                        'net_price'      => 55.32,
                        'shipping_price' => 4.16,
                        'ordered'        => 17.,
                        'sold'           => 3.,
                        'subject'        => 'subject5',
                    ],
                    // Total weight:  13.60  |  Weighting: 0,017353579175705 (0,2950108459869848)
                    // Total price:  940.44  |  Weighting: 0,0253448726113191 (0,430862834392424)
                    // Tax:          188.09
                ],
                [
                    '_reference' => 'supplier_order4_item2',
                    '_weighting' => [
                        'default'  => 0.0281995661605206,
                        'weight'   => 0.0281995661605206,
                        'price'    => 0.0227654866243030,
                        'quantity' => 0.0238095238095238,
                    ],
                    'weight'     => 1.3,
                    'price'      => 49.69,
                    'quantity'   => 25.,
                    'unit'       => [
                        '_reference'     => 'stock_unit8',
                        'net_price'      => 49.69,
                        'shipping_price' => 6.77,
                        'ordered'        => 25.,
                        'sold'           => 2.,
                        'subject'        => 'subject6',
                    ],
                    // Total weight:   32.50  |  Weighting: 0,0281995661605206 (0,7049891540130152)
                    // Total price:  1242.25  |  Weighting: 0,022765486624303 (0,569137165607576)
                    // Tax:           124.22
                ],
                // Total price:   2182.69
                // Total weight:    46.10
                // Total quantity:  42
            ],
        ];
    }

    public static function shipment_method_UPS(): array
    {
        return [
            '_type'      => 'shipmentMethod',
            '_reference' => Fixture::SHIPMENT_METHOD_UPS,
            'name'       => 'UPS',
            'enabled'    => true,
            'available'  => true,
            'platform'   => 'ups',
            'gateway'    => 'ups',
        ];
    }

    public static function shipment_method_DHL(): array
    {
        return [
            '_type'      => 'shipmentMethod',
            '_reference' => Fixture::SHIPMENT_METHOD_DHL,
            'name'       => 'DHL',
            'enabled'    => true,
            'available'  => false,
            'platform'   => 'dhl',
            'gateway'    => 'dhl',
        ];
    }

    public static function shipment_zone_FR(): array
    {
        return [
            '_type'      => 'shipmentZone',
            '_reference' => Fixture::SHIPMENT_ZONE_FR,
            'name'       => 'France',
            'countries'  => [Fixture::COUNTRY_FR],
            'prices'     => [
                [
                    '_reference' => 'shipment_price_UPS_FR_1',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 1.,
                    'price'      => 6.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_FR_2',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 10.,
                    'price'      => 11.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_FR_3',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 20.,
                    'price'      => 16.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_FR_4',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 30.,
                    'price'      => 19.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_FR_1',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 1.,
                    'price'      => 5.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_FR_2',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 8.,
                    'price'      => 9.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_FR_3',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 16.,
                    'price'      => 12.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_FR_4',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 24.,
                    'price'      => 14.,
                ],
            ],
        ];
    }

    public static function shipment_zone_EU(): array
    {
        return [
            '_type'      => 'shipmentZone',
            '_reference' => Fixture::SHIPMENT_ZONE_EU,
            'name'       => 'Europe',
            'countries'  => [Fixture::COUNTRY_ES],
            'prices'     => [
                [
                    '_reference' => 'shipment_price_UPS_EU_1',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 1.,
                    'price'      => 10.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_EU_2',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 10.,
                    'price'      => 16.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_EU_3',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 20.,
                    'price'      => 21.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_EU_4',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 30.,
                    'price'      => 26.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_EU_1',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 1.,
                    'price'      => 10.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_EU_2',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 8.,
                    'price'      => 13.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_EU_3',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 16.,
                    'price'      => 18.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_EU_4',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 24.,
                    'price'      => 22.,
                ],
            ],
        ];
    }

    public static function shipment_zone_US(): array
    {
        return [
            '_type'      => 'shipmentZone',
            '_reference' => Fixture::SHIPMENT_ZONE_US,
            'name'       => 'United States of America',
            'countries'  => [Fixture::COUNTRY_US],
            'prices'     => [
                [
                    '_reference' => 'shipment_price_UPS_US_1',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 1.,
                    'price'      => 12.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_US_2',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 10.,
                    'price'      => 18.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_US_3',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 20.,
                    'price'      => 24.,
                ],
                [
                    '_reference' => 'shipment_price_UPS_US_4',
                    'method'     => Fixture::SHIPMENT_METHOD_UPS,
                    'weight'     => 30.,
                    'price'      => 30.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_US_1',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 1.,
                    'price'      => 11.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_US_2',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 8.,
                    'price'      => 16.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_US_3',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 16.,
                    'price'      => 20.,
                ],
                [
                    '_reference' => 'shipment_price_DHL_US_4',
                    'method'     => Fixture::SHIPMENT_METHOD_DHL,
                    'weight'     => 24.,
                    'price'      => 24.,
                ],
            ],
        ];
    }
}
