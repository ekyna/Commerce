<?php

namespace Ekyna\Component\Commerce\Tests;

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
            'items'           => [
                [
                    '_reference' => 'order1_item1', // id: 1
                    'quantity'   => 3,
                    'price'      => 32.59,
                    'discounts'  => [7],
                    'taxes'      => [20],
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
                            'quantity'   => 5,
                            'price'      => 12.34,
                            'discounts'  => [5],
                            'taxes'      => [5.5],
                            // Default                   // Revenue (invoiced: 20, credit: 5)
                            // Gross:    246.80          // Gross:    185.10
                            // Discount:  12.34          // Discount:   9.25
                            // Base:     234.46          // Base:     175.85
                            // Tax:       12.90          // Tax:        9.67
                            // Total:    247.36          // Total:    185.52
                        ],
                        [
                            '_reference' => 'order1_item2_2', // id: 4
                            'quantity'   => 2,
                            'price'      => 47.99,
                            'discounts'  => [10],
                            'taxes'      => [5.5],
                            'children'   => [
                                [
                                    '_reference' => 'order1_item2_2_1', // id: 5
                                    'quantity'   => 2,
                                    'price'      => 3.99,
                                    'private'    => true,
                                ],
                                [
                                    '_reference' => 'order1_item2_2_2', // id: 6
                                    'quantity'   => 3,
                                    'price'      => 4.99,
                                    'private'    => true,
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
                            'quantity' => 2,
                            'price'    => 4.14,
                            'private'  => true,
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
            'discounts'       => [12],
            'taxes'           => [20],
            'shipment_amount' => 15.26,
            'grand_total'     => 900.52,
            'invoice_total'   => 900.52, // So that $order->isFullyInvoiced() return true
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
                    // Weight:     5.6
                    // Gross:    312.80
                    // Discount:   0.00
                    // Base:     312.80
                    // Tax:       31.28
                    // Total:    344.08
                    // Margin:    92.82 (29.67%) | 26.58 (8,50%)
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
                                '_reference' => 'stock_unit9',
                            ],
                            'sold' => 3.,
                        ],
                    ],
                    // Weight:    13.0
                    // Gross:    695.00
                    // Discount:  83.40
                    // Base:     611.60
                    // Tax:       61.16
                    // Total:    672.76
                    // Margin:   107.40 (17.56%) | 45.96 (7.51%)
                    // -> if subject2 cost price price is guessed as 52.31 EUR (+shipping: 56.69)
                ],
                [
                    //'_id'         => 3,
                    '_reference' => 'order3_item3',
                    // TODO 'subject'     => 'subject7',
                    'quantity'   => 2,
                    'price'      => 0,
                    'weight'     => 0,
                    'compound'   => true, // TODO
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
                            // Weight:     8.0
                            // Gross:    826.00
                            // Discount:  41.30
                            // Base:     784.70
                            // Tax:      156.94
                            // Total:    941.64
                            // Margin:   254.88 (32.48%) | 137.05 (17.47%)
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
                                    'unit'    => 'stock_unit3', // (price: 40.78, shipping: 1.69)
                                    'sold'    => 4.,
                                    'shipped' => 0.,
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
                                            'unit'    => 'stock_unit1', // (price: 10.78, shipping: 4.25)
                                            'sold'    => 16.,
                                            'shipped' => 0.,
                                        ],
                                    ],
                                    // Weight:    12.8
                                    // Gross:    398.4
                                ],
                            ],
                            // Weight:    14.8
                            // Gross:    660.00
                            // Discount:  79.20
                            // Base:     580.80
                            // Tax:      116.16
                            // Total:    696.96
                            // Margin:   245.20 (42.22%) | 170.44 (29.35%)
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
            'shipment_amount' => 12.34, // Margin: null | -34.66 (-280.87%)
            'weight_total'    => 60.,
            // Gross:    2289.90
            // Discount:    0.00
            // Base:     2302.24
            // Tax:       368.01
            // Total:    2670.25
            // Margin:
            //   [default] sell: 2289.90, purchase: 1589.60 => 700.30 (30.58%)
            //   [profit]  sell: 2302.24, purchase: 1956.87 => 345.37 (15.00%)
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

    public static function supplier_order1(): array
    {
        return [
            '_reference'     => 'supplier_order1',
            '_type'          => 'supplierOrder',
            'supplier'       => [
                'tax' => Fixture::TAX_FR_NORMAL,
            ],
            'currency'       => Fixture::CURRENCY_EUR,
            'shipping_cost'  => 320.,
            'discount_total' => 150.,
            'items'          => [
                [
                    '_reference' => 'supplier_order1_item1',
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
                    // Total weight:  51.2 | Weighting: ‭0,0132669983416252‬‬ (‭0,8490878938640133‬‬)
                    // Total price: 789.76 | Weighting: 0,010394029750173‬‬ (‭0,665217904011051‬‬)
                    // Discount: ‭   ‭99,78268560165765‬
                    // Base: ‭       ‭689,9773143983424‬
                    // Tax:         ‭138.00
                ],
                [
                    '_reference' => 'supplier_order1_item2',
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
                    // Total weight:  9.10 | Weighting: ‭0,021558872305141‬‬ (‭0,1509121061359867‬‬)
                    // Total price: 397.46 | Weighting: ‭0,047826013712707‬‬ (‭0,334782095988949‬)
                    // Discount:     ‭50,21731439834235‬
                    // Base:  ‭      ‭347,2426856016577‬‬  (‬)
                    // Tax:          ‭34,72
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
            'supplier'      => [],
            'currency'      => Fixture::CURRENCY_USD,
            'exchange_rate' => 1.12,
            'exchange_date' => 'now',
            'shipping_cost' => 300.,
            'items'         => [
                [
                    '_reference' => 'supplier_order2_item1',
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
                    // Total weight:   16.50  |  Weighting: ‭0,0063131313131313‬‬ (‭0,2083333333333333‬)
                    // Total price : 1507.11  |  Weighting: ‭0,0160597237460264‬‬ (‭0,5299708836188708‬)
                ],
                [
                    '_reference' => 'supplier_order2_item2',
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
                    // Total weight:   62.70  |  Weighting: ‭0,0138888888888889‬‬ (‭0,7916666666666667‬‬)
                    // Total price:  1336.65  |  Weighting: ‭0,0082461248487917‬‬ (‭0,4700291163811292‬)
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
                    // Total weight:   47.20  |  Weighting: ‭0,0074487895716946‬‬‬ (‭0,4394785847299814‬‬‬)
                    // Total price:  3833.23  |  Weighting: ‭0,0091841552471396‬‬‬‬ (‭0,5418651595812353‬‬‬)
                ],
                [
                    '_reference' => 'supplier_order3_item2',
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
                    // Total weight:   60.20  |  Weighting: ‭0,0130353817504655‬‬‬ (‭0,5605214152700186‬‬‬)
                    // Total price:  3240.91  |  Weighting: ‭0,0106542986143899‬‬‬‬ (‭0,4581348404187647‬‬‬)
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
            'supplier'      => ['tax' => Fixture::TAX_FR_NORMAL],
            'currency'      => Fixture::CURRENCY_EUR,
            'shipping_cost' => 240.,
            'items'         => [
                [
                    '_reference' => 'supplier_order4_item1',
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
                    // Total weight:  13.60  |  Weighting: ‭0,017353579175705‬‬‬‬ (‭0,2950108459869848‬‬‬‬)
                    // Total price:  940.44  |  Weighting: ‭0,0253448726113191‬‬‬‬‬ (‭0,430862834392424‬‬‬‬)
                    // Tax:          188.09
                ],
                [
                    '_reference' => 'supplier_order4_item2',
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
                    // Total weight:   32.50  |  Weighting: ‭0,0281995661605206‬‬‬‬ (‭0,7049891540130152‬‬‬‬)
                    // Total price:  1242.25  |  Weighting: ‭0,022765486624303‬‬‬‬‬ (‭0,569137165607576‬‬‬‬)
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
