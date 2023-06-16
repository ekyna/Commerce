<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Invoice\Calculator;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculator;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use Generator;
use PHPUnit\Framework\MockObject\MockObject;

use function array_keys;
use function array_map;
use function array_replace;
use function array_values;

/**
 * Class InvoiceSubjectCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSubjectCalculatorTest extends TestCase
{
    private ShipmentSubjectCalculatorInterface|MockObject|null $shipmentCalculator;
    private ?InvoiceSubjectCalculator                          $invoiceCalculator;

    protected function setUp(): void
    {
        $this->shipmentCalculator = $this->createMock(ShipmentSubjectCalculatorInterface::class);
        $this->invoiceCalculator = new InvoiceSubjectCalculator($this->getCurrencyConverter());
        $this->invoiceCalculator->setShipmentCalculator($this->shipmentCalculator);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->invoiceCalculator = null;
        $this->shipmentCalculator = null;
    }

    /**
     * @dataProvider provideIsInvoiced
     */
    public function testIsInvoiced(array $order, array $result): void
    {
        Fixture::order($order);

        foreach ($result as $reference => $expected) {
            self::assertEquals($expected, $this->invoiceCalculator->isInvoiced(Fixture::get($reference)));
        }
    }

    public function provideIsInvoiced(): Generator
    {
        yield 'Item not invoiced' => [
            [
                'items' => [
                    ['_reference' => '#item', 'quantity' => 5],
                ],
            ],
            ['#item' => false],
        ];

        yield 'Item invoiced' => [
            [
                'items'    => [
                    ['_reference' => '#item', 'quantity' => 5],
                ],
                'invoices' => [
                    ['lines' => [['item' => '#item', 'quantity' => 5]]],
                ],
            ],
            ['#item' => true],
        ];

        yield 'Child item not invoiced' => [
            [
                'items' => [
                    [
                        'quantity' => 5,
                        'children' => [
                            ['_reference' => '#item', 'quantity' => 10],
                        ],
                    ],
                ],
            ],
            ['#item' => false],
        ];

        yield 'Child item invoiced' => [
            [
                'items'    => [
                    [
                        'quantity' => 5,
                        'children' => [
                            ['_reference' => '#item', 'quantity' => 10],
                        ],
                    ],
                ],
                'invoices' => [
                    ['lines' => [['item' => '#item', 'quantity' => 5]]],
                ],
            ],
            ['#item' => true],
        ];

        yield 'Adjustment not invoiced' => [
            [
                'discounts' => [
                    [
                        '_reference' => '#adjustment',
                        'amount'     => 10,
                    ],
                ],
            ],
            ['#adjustment' => false],
        ];

        yield 'Adjustment invoiced' => [
            [
                'discounts' => [
                    [
                        '_reference' => '#adjustment',
                        'amount'     => 10,
                    ],
                ],
                'invoices'  => [
                    ['lines' => [['adjustment' => '#adjustment', 'quantity' => 1]]],
                ],
            ],
            ['#adjustment' => true],
        ];
    }

    /**
     * @dataProvider provideCalculateInvoiceableQuantity
     */
    public function testCalculateInvoiceableQuantity(array $order, array $result): void
    {
        Fixture::clear();

        Fixture::order($order);

        foreach ($result as $reference => $expected) {
            $element = Fixture::get($reference);

            $actual = $this->invoiceCalculator->calculateInvoiceableQuantity($element);

            self::assertEquals(new Decimal($expected), $actual);
        }
    }

    public function provideCalculateInvoiceableQuantity(): Generator
    {
        yield 'Sale item not invoiced' => [
            [
                'items' => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 10,
                    ],
                ],
            ],
            [
                '#item1' => 10,
            ],
        ];

        yield 'Sale item partially invoiced' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 10,
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1',
                                'quantity' => 4,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1' => 6,
            ],
        ];

        yield 'Sale item fully invoiced' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 10,
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1',
                                'quantity' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1' => 0,
            ],
        ];

        yield 'Sale item (child) partially invoiced' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 2,
                        'children'   => [
                            [
                                '_reference' => '#item1.1',
                                'quantity'   => 5,
                            ],
                        ],
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1.1',
                                'quantity' => 4,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1.1' => 6,
            ],
        ];

        yield 'Sale item partially invoiced (multiple invoices)' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 5,
                        'children'   => [
                            [
                                '_reference' => '#item1.1',
                                'quantity'   => 10,
                            ],
                        ],
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1.1',
                                'quantity' => 30,
                            ],
                        ],
                    ],
                    [
                        'lines' => [
                            [
                                'item'     => '#item1.1',
                                'quantity' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1.1' => 10,
            ],
        ];

        yield 'Sale adjustment not invoiced' => [
            [
                'discounts' => [
                    [
                        '_reference' => '#adjustment1',
                        'amount'     => 10,
                    ],
                ],
            ],
            [
                '#adjustment1' => 1,
            ],
        ];

        yield 'Sale adjustment invoiced' => [
            [
                'discounts' => [
                    [
                        '_reference' => '#adjustment1',
                    ],
                ],
                'invoices'  => [
                    [
                        'lines' => [
                            [
                                'adjustment' => '#adjustment1',
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#adjustment1' => 1, // Discounts must be dispatched into all invoices
            ],
        ];

        yield 'Sale (shipment) not invoiced' => [
            [
                '_reference' => '#order1',
            ],
            [
                '#order1' => 1,
            ],
        ];

        yield 'Sale (shipment) invoiced' => [
            [
                '_reference' => '#order1',
                'invoices'   => [
                    [
                        'lines' => [
                            [
                                'order' => '#order1',
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#order1' => 0,
            ],
        ];
    }

    /**
     * @dataProvider provideCalculateInvoicedQuantity
     */
    public function testCalculateInvoicedQuantity(array $order, array $result): void
    {
        Fixture::clear();

        Fixture::order($order);

        foreach ($result as $reference => $expected) {
            $element = Fixture::get($reference);

            $actual = $this->invoiceCalculator->calculateInvoicedQuantity($element);

            self::assertEquals(new Decimal($expected), $actual);
        }
    }

    public function provideCalculateInvoicedQuantity(): Generator
    {
        yield 'Sale item not invoiced' => [
            [
                'items' => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 10,
                    ],
                ],
            ],
            [
                '#item1' => 0,
            ],
        ];

        yield 'Sale item partially invoiced' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 10,
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1',
                                'quantity' => 4,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1' => 4,
            ],
        ];

        yield 'Sale item fully invoiced' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 10,
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1',
                                'quantity' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1' => 10,
            ],
        ];

        yield 'Sale item (child) partially invoiced' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 2,
                        'children'   => [
                            [
                                '_reference' => '#item1.1',
                                'quantity'   => 5,
                            ],
                        ],
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1.1',
                                'quantity' => 4,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1.1' => 4,
            ],
        ];

        yield 'Sale item partially invoiced (multiple invoices)' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 5,
                        'children'   => [
                            [
                                '_reference' => '#item1.1',
                                'quantity'   => 10,
                            ],
                        ],
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1.1',
                                'quantity' => 30,
                            ],
                        ],
                    ],
                    [
                        'lines' => [
                            [
                                'item'     => '#item1.1',
                                'quantity' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1.1' => 40,
            ],
        ];

        yield 'Sale adjustment not invoiced' => [
            [
                'discounts' => [
                    [
                        '_reference' => '#adjustment1',
                        'amount'     => 10,
                    ],
                ],
            ],
            [
                '#adjustment1' => 0,
            ],
        ];

        yield 'Sale adjustment invoiced' => [
            [
                'discounts' => [
                    [
                        '_reference' => '#adjustment1',
                    ],
                ],
                'invoices'  => [
                    [
                        'lines' => [
                            [
                                'adjustment' => '#adjustment1',
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#adjustment1' => 1,
            ],
        ];

        yield 'Sale (shipment) not invoiced' => [
            [
                '_reference' => '#order1',
            ],
            [
                '#order1' => 0,
            ],
        ];

        yield 'Sale (shipment) invoiced' => [
            [
                '_reference' => '#order1',
                'invoices'   => [
                    [
                        'lines' => [
                            [
                                'order' => '#order1',
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#order1' => 1,
            ],
        ];
    }

    /**
     * @param array $order
     * @param array $result
     *
     * @dataProvider provideCalculateCreditedQuantity
     */
    public function testCalculateCreditedQuantity(array $order, array $result): void
    {
        Fixture::order($order);

        foreach ($result as $reference => $set) {
            $subject = Fixture::get($reference);
            foreach ($set as [$adjustment, $expected]) {
                $message = '';
                if (is_bool($adjustment)) {
                    $message = $adjustment ? 'Only adjustments' : 'Excluding adjustments';
                }

                $calculated = $this->invoiceCalculator->calculateCreditedQuantity($subject, null, $adjustment);

                self::assertEquals(new Decimal($expected), $calculated, $message);
            }
        }
    }

    public function provideCalculateCreditedQuantity(): Generator
    {
        yield 'Item not invoiced' => [
            [
                'items' => [
                    ['_reference' => '#item', 'quantity' => 10],
                ],
            ],
            [
                '#item' => [
                    [null, 0],
                    [true, 0],
                    [false, 0],
                ],
            ],
        ];

        yield 'Item invoiced' => [
            [
                'items'    => [
                    ['_reference' => '#item', 'quantity' => 10],
                ],
                'invoices' => [
                    ['lines' => [['item' => '#item', 'quantity' => 5]]],
                ],
            ],
            [
                '#item' => [
                    [null, 0],
                    [true, 0],
                    [false, 0],
                ],
            ],
        ];

        yield 'Item credited' => [
            [
                'items'    => [
                    ['_reference' => '#item', 'quantity' => 10],
                ],
                'invoices' => [
                    ['lines' => [['item' => '#item', 'quantity' => 10]]],
                    ['lines' => [['item' => '#item', 'quantity' => 5]], 'credit' => true],
                ],
            ],
            [
                '#item' => [
                    [null, 5],
                    [true, 0],
                    [false, 5],
                ],
            ],
        ];

        yield 'Item credited (ignoring stock)' => [
            [
                'items'    => [
                    ['_reference' => '#item', 'quantity' => 10],
                ],
                'invoices' => [
                    ['lines' => [['item' => '#item', 'quantity' => 10]]],
                    ['lines' => [['item' => '#item', 'quantity' => 5]], 'credit' => true, 'ignore_stock' => true],
                ],
            ],
            [
                '#item' => [
                    [null, 5],
                    [true, 5],
                    [false, 0],
                ],
            ],
        ];

        yield 'Child invoiced' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item',
                        'quantity'   => 2,
                        'children'   => [
                            ['_reference' => '#child', 'quantity' => 5],
                        ],
                    ],
                ],
                'invoices' => [
                    ['lines' => [['item' => '#child', 'quantity' => 5]]],
                ],
            ],
            [
                '#item' => [
                    [null, 0],
                    [true, 0],
                    [false, 0],
                ],
            ],
        ];

        yield 'Child credited' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item',
                        'quantity'   => 2,
                        'children'   => [
                            ['_reference' => '#child', 'quantity' => 5],
                        ],
                    ],
                ],
                'invoices' => [
                    ['lines' => [['item' => '#child', 'quantity' => 5]]],
                    ['lines' => [['item' => '#child', 'quantity' => 5]]],
                    ['lines' => [['item' => '#child', 'quantity' => 5]], 'credit' => true],
                ],
            ],
            [
                '#child' => [
                    [null, 5],
                    [true, 0],
                    [false, 5],
                ],
            ],
        ];

        yield 'Child credited (ignoring stock)' => [
            [
                'items'    => [
                    [
                        '_reference' => '#item',
                        'quantity'   => 2,
                        'children'   => [
                            ['_reference' => '#child', 'quantity' => 5],
                        ],
                    ],
                ],
                'invoices' => [
                    ['lines' => [['item' => '#child', 'quantity' => 5]]],
                    ['lines' => [['item' => '#child', 'quantity' => 5]]],
                    ['lines' => [['item' => '#child', 'quantity' => 5]], 'credit' => true, 'ignore_stock' => true],
                ],
            ],
            [
                '#child' => [
                    [null, 5],
                    [true, 5],
                    [false, 0],
                ],
            ],
        ];

        yield 'Adjustment not invoiced' => [
            [
                'discounts' => [
                    ['_reference' => '#discount',],
                ],
            ],
            [
                '#discount' => [
                    [null, 0],
                    [true, 0],
                    [false, 0],
                ],
            ],
        ];

        yield 'Adjustment invoiced' => [
            [
                'discounts' => [
                    ['_reference' => '#discount',],
                ],
                'invoices'  => [
                    ['lines' => [['adjustment' => '#discount', 'quantity' => 1]]],
                ],
            ],
            [
                '#discount' => [
                    [null, 0],
                    [true, 0],
                    [false, 0],
                ],
            ],
        ];

        yield 'Adjustment credited' => [
            [
                'discounts' => [
                    ['_reference' => '#discount',],
                ],
                'invoices'  => [
                    ['lines' => [['adjustment' => '#discount', 'quantity' => 1]]],
                    ['lines' => [['adjustment' => '#discount', 'quantity' => 1]], 'credit' => true],
                ],
            ],
            [
                '#discount' => [
                    [null, 1],
                    [true, 0],
                    [false, 1],
                ],
            ],
        ];

        yield 'Adjustment credited (ignoring stock)' => [
            [
                'discounts' => [
                    ['_reference' => '#discount',],
                ],
                'invoices'  => [
                    ['lines' => [['adjustment' => '#discount', 'quantity' => 1]]],
                    [
                        'lines'        => [['adjustment' => '#discount', 'quantity' => 1]],
                        'credit'       => true,
                        'ignore_stock' => true,
                    ],
                ],
            ],
            [
                '#discount' => [
                    [null, 1],
                    [true, 1],
                    [false, 0],
                ],
            ],
        ];

        yield 'Shipment not invoiced' => [
            [
                '_reference' => '#order',
            ],
            [
                '#order' => [
                    [null, 0],
                    [true, 0],
                    [false, 0],
                ],
            ],
        ];

        yield 'Shipment invoiced' => [
            [
                '_reference' => '#order',
                'invoices'   => [
                    ['lines' => [['order' => '#order', 'quantity' => 1]]],
                ],
            ],
            [
                '#order' => [
                    [null, 0],
                    [true, 0],
                    [false, 0],
                ],
            ],
        ];

        yield 'Shipment credited' => [
            [
                '_reference' => '#order',
                'invoices'   => [
                    ['lines' => [['order' => '#order', 'quantity' => 1]]],
                    ['lines' => [['order' => '#order', 'quantity' => 1]], 'credit' => true],
                ],
            ],
            [
                '#order' => [
                    [null, 1],
                    [true, 0],
                    [false, 1],
                ],
            ],
        ];

        yield 'Shipment credited (ignoring stock)' => [
            [
                '_reference' => '#order',
                'invoices'   => [
                    ['lines' => [['order' => '#order', 'quantity' => 1]]],
                    ['lines' => [['order' => '#order', 'quantity' => 1]], 'credit' => true, 'ignore_stock' => true],
                ],
            ],
            [
                '#order' => [
                    [null, 1],
                    [true, 1],
                    [false, 0],
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideCalculateInvoiceTotal
     */
    public function testCalculateInvoiceTotal(array $order, array $amounts): void
    {
        $order = Fixture::order($order);

        foreach ($amounts as $currency => $expected) {
            $actual = $this->invoiceCalculator->calculateInvoiceTotal($order, $currency);
            self::assertEquals(new Decimal($expected), $actual);
        }
    }

    public function provideCalculateInvoiceTotal(): Generator
    {
        yield 'Case 1' => [
            [
                'invoices' => [
                    ['grand_total' => 100],
                    ['grand_total' => 50],
                ],
            ],
            [
                Fixture::CURRENCY_EUR => '150',
                Fixture::CURRENCY_USD => '187.50',
            ],
        ];

        yield 'Case 2' => [
            [
                'currency' => Fixture::CURRENCY_USD,
                'invoices' => [
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 100],
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 50],
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 50, 'credit' => true],
                ],
            ],
            [
                Fixture::CURRENCY_EUR => '120',
                Fixture::CURRENCY_USD => '150',
            ],
        ];

        yield 'Case 3' => [
            [
                'currency'      => Fixture::CURRENCY_USD,
                'exchange_rate' => '1.12',
                'invoices'      => [
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 100],
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 50],
                ],
            ],
            [
                Fixture::CURRENCY_EUR => '133.93',
                Fixture::CURRENCY_USD => '150',
            ],
        ];
    }

    /**
     * @dataProvider provideCalculateCreditTotal
     */
    public function testCalculateCreditTotal(array $order, array $amounts): void
    {
        $order = Fixture::order($order);

        foreach ($amounts as $currency => $expected) {
            $actual = $this->invoiceCalculator->calculateCreditTotal($order, $currency);
            self::assertEquals(new Decimal($expected), $actual);
        }
    }

    public function provideCalculateCreditTotal(): Generator
    {
        yield 'Case 1' => [
            [
                'invoices' => [
                    ['grand_total' => 100],
                    ['grand_total' => 50],
                    ['grand_total' => 100, 'credit' => true],
                    ['grand_total' => 50, 'credit' => true],
                ],
            ],
            [
                Fixture::CURRENCY_EUR => '150',
                Fixture::CURRENCY_USD => '187.50',
            ],
        ];

        yield 'Case 2' => [
            [
                'currency' => Fixture::CURRENCY_USD,
                'invoices' => [
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 100],
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 50],
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 50, 'credit' => true],
                ],
            ],
            [
                Fixture::CURRENCY_EUR => '40',
                Fixture::CURRENCY_USD => '50',
            ],
        ];

        yield 'Case 3' => [
            [
                'currency'      => Fixture::CURRENCY_USD,
                'exchange_rate' => '1.12',
                'invoices'      => [
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 100],
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 50],
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 100, 'credit' => true],
                ],
            ],
            [
                Fixture::CURRENCY_EUR => '89.29',
                Fixture::CURRENCY_USD => '100',
            ],
        ];
    }

    /**
     * @dataProvider provideCalculateCreditTotal
     */
    public function testCalculateSoldQuantity(array $order, array $amounts): void
    {
        $order = Fixture::order($order);

        foreach ($amounts as $currency => $expected) {
            $actual = $this->invoiceCalculator->calculateCreditTotal($order, $currency);
            self::assertEquals(new Decimal($expected), $actual);
        }
    }

    public function provideCalculateSoldQuantity(): Generator
    {
        yield 'Case 1' => [
            array_replace(Data::order1(), [
                'invoices' => [
                    [
                        'lines' => [
                            ['item' => ['_reference' => 'order1_item1'], 'quantity' => 3],
                            ['item' => ['_reference' => 'order1_item2_1'], 'quantity' => 20],
                            ['item' => ['_reference' => 'order1_item2_2'], 'quantity' => 8],
                            ['item' => ['_reference' => 'order1_item2_2_1'], 'quantity' => 16],
                            ['item' => ['_reference' => 'order1_item2_2_2'], 'quantity' => 24],
                            ['item' => ['_reference' => 'order1_item3'], 'quantity' => 6],
                            ['item' => ['_reference' => 'order1_item3_1'], 'quantity' => 12],
                        ]
                    ],
                    ['grand_total' => 50],
                    ['grand_total' => 100, 'credit' => true],
                    ['grand_total' => 50, 'credit' => true],
                ],
            ]),
            [
                Fixture::CURRENCY_EUR => '150',
                Fixture::CURRENCY_USD => '187.50',
            ],
        ];
    }

    /**
     * @dataProvider provideBuildInvoiceQuantityMap
     */
    public function testBuildInvoiceQuantityMap(array $expected, array $order, array $shipment = []): void
    {
        $expected = array_map(fn(array $item) => array_map(fn($q) => new Decimal($q), $item), $expected);

        Fixture::clear();

        $order = Fixture::order($order);

        $this->configureShipmentCalculator($shipment);

        $actual = $this->invoiceCalculator->buildInvoiceQuantityMap($order);

        self::assertEquals($expected, $actual);
    }

    public function provideBuildInvoiceQuantityMap(): Generator
    {
        yield [
            [
                1 => [
                    'total'    => 10,
                    'invoiced' => 0,
                    'credited' => 0,
                    'shipped'  => 0,
                    'returned' => 0,
                    'adjusted' => 0,
                ],
            ],
            [
                'items' => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 10,
                    ],
                ],
            ],
            [
                '#item1' => [
                    'shipped'  => 0,
                    'returned' => 0,
                ],
            ],
        ];

        yield [
            [
                1 => [
                    'total'    => 5,
                    'invoiced' => 5,
                    'credited' => 0,
                    'shipped'  => 0,
                    'returned' => 0,
                    'adjusted' => 0,
                ],
                3 => [
                    'total'    => 50,
                    'invoiced' => 40,
                    'credited' => 10,
                    'shipped'  => 0,
                    'returned' => 0,
                    'adjusted' => 0,
                ],
            ],
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 5,
                    ],
                    [
                        '_reference' => '#item2',
                        'quantity'   => 5,
                        'compound'   => true,
                        'children'   => [
                            [
                                '_reference' => '#item2.1',
                                'quantity'   => 10,
                            ],
                        ],
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1',
                                'quantity' => 5,
                            ],
                            [
                                'item'     => '#item2.1',
                                'quantity' => 30,
                            ],
                        ],
                    ],
                    [
                        'lines' => [
                            [
                                'item'     => '#item2.1',
                                'quantity' => 10,
                            ],
                        ],
                    ],
                    [
                        'credit' => true,
                        'lines'  => [
                            [
                                'item'     => '#item2.1',
                                'quantity' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1'   => [],
                '#item2.1' => [],
            ],
        ];

        // Credit that cancels sold quantity (not shipped)
        yield [
            [
                1 => [
                    'total'    => 5,
                    'invoiced' => 5,
                    'credited' => 2,
                    'shipped'  => 3,
                    'returned' => 0,
                    'adjusted' => 0,
                ],
            ],
            [
                'items'    => [
                    [
                        '_reference' => '#item1',
                        'quantity'   => 5,
                    ],
                ],
                'invoices' => [
                    [
                        'lines' => [
                            [
                                'item'     => '#item1',
                                'quantity' => 5,
                            ],
                        ],
                    ],
                    [
                        'credit' => true,
                        'lines'  => [
                            [
                                'item'     => '#item1',
                                'quantity' => 2,
                            ],
                        ],
                    ],
                ],
            ],
            [
                '#item1' => [
                    'shipped' => 3,
                ],
            ],
        ];
    }

    /**
     * Configures the shipment calculator mock.
     */
    protected function configureShipmentCalculator(array $map): void
    {
        if (empty($map)) {
            return;
        }

        $subjects = array_map(fn(string $ref) => [Fixture::get($ref)], array_keys($map));
        $shipped = array_map(fn(array $data) => new Decimal($data['shipped'] ?? 0), array_values($map));
        $returned = array_map(fn(array $data) => new Decimal($data['returned'] ?? 0), array_values($map));

        $this
            ->shipmentCalculator
            ->method('calculateShippedQuantity')
            ->withConsecutive(...$subjects)
            ->willReturnOnConsecutiveCalls(...$shipped);

        $this
            ->shipmentCalculator
            ->method('calculateReturnedQuantity')
            ->withConsecutive(...$subjects)
            ->willReturnOnConsecutiveCalls(...$returned);
    }
}
