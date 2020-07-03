<?php

namespace Ekyna\Component\Commerce\Tests\Invoice\Calculator;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculator;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Shipment\Calculator\ShipmentSubjectCalculatorInterface;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class InvoiceSubjectCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Invoice\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class InvoiceSubjectCalculatorTest extends TestCase
{
    /**
     * @var InvoiceSubjectCalculator
     */
    private $invoiceCalculator;

    /**
     * @var ShipmentSubjectCalculatorInterface|MockObject
     */
    private $shipmentCalculator;

    protected function setUp(): void
    {
        $this->shipmentCalculator = $this->createMock(ShipmentSubjectCalculatorInterface::class);
        $this->invoiceCalculator  = new InvoiceSubjectCalculator($this->getCurrencyConverter());
        $this->invoiceCalculator->setShipmentCalculator($this->shipmentCalculator);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->invoiceCalculator = null;
        $this->shipmentCalculator = null;
    }

    /**
     * @param array $order
     * @param array $result
     *
     * @dataProvider provide_isInvoiced
     */
    public function test_isInvoiced(array $order, array $result): void
    {
        Fixture::order($order);

        foreach ($result as $reference => $expected) {
            /** @noinspection PhpParamsInspection */
            $this->assertEquals($expected, $this->invoiceCalculator->isInvoiced(Fixture::get($reference)));
        }
    }

    public function provide_isInvoiced(): \Generator
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
                /*'invoices'    => [
                    ['lines' => [['item' => '#item', 'quantity' => 5]]],
                ],*/
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
     * @param float $expected
     * @param mixed $element
     *
     * @dataProvider provide_calculateInvoiceableQuantity
     *
     * @TODO         Rework like test_isInvoiced
     */
    public function test_calculateInvoiceableQuantity(float $expected, object $element): void
    {
        $actual = $this->invoiceCalculator->calculateInvoiceableQuantity($element);

        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateInvoiceableQuantity(): \Generator
    {
        // Items
        $orderItem = Fixture::orderItem([
            'order'    => [],
            'quantity' => 10,
        ]);
        yield [10, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 10,
        ]);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderItem,
            'quantity' => 5,
        ]);
        yield [5, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 10,
        ]);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderItem,
            'quantity' => 10, // TODO
        ]);
        yield [0, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 2,
        ]);
        $childItem = Fixture::orderItem([
            'quantity' => 5,
        ])->setParent($orderItem);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 5,
        ]);
        yield [5, $childItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 5,
        ]);
        $childItem = Fixture::orderItem([
            'quantity' => 10,
        ])->setParent($orderItem);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 30,
        ]);
        $invoice = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 10,
        ]);
        yield [10, $childItem];

        // Adjustments
        $order           = Fixture::order();
        $orderAdjustment = Fixture::orderDiscountAdjustment(10)->setOrder($order);
        yield [1, $orderAdjustment];

        $order           = Fixture::order();
        $orderAdjustment = Fixture::orderDiscountAdjustment(10)->setOrder($order);
        $invoice         = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderAdjustment,
            'quantity' => 1,
        ]);
        yield [1, $orderAdjustment];

        // Shipment
        yield [1, Fixture::order()];

        $order   = Fixture::order();
        $invoice = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $order,
            'quantity' => 1,
        ]);
        yield [0, $order];
    }

    /**
     * @param float $expected
     * @param mixed $element
     *
     * @dataProvider provide_calculateInvoicedQuantity
     *
     * @TODO         Rework like test_isInvoiced
     */
    public function test_calculateInvoicedQuantity(float $expected, object $element): void
    {
        $actual = $this->invoiceCalculator->calculateInvoicedQuantity($element);

        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateInvoicedQuantity(): \Generator
    {
        // Items
        $orderItem = Fixture::orderItem([
            'order'    => [],
            'quantity' => 10,
        ]);
        yield [0, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 10,
        ]);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderItem,
            'quantity' => 5,
        ]);
        yield [5, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 10,
        ]);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderItem,
            'quantity' => 10,
        ]);
        yield [10, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 2,
        ]);
        $childItem = Fixture::orderItem([
            'quantity' => 5,
        ])->setParent($orderItem);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 5,
        ]);
        yield [5, $childItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 5,
        ]);
        $childItem = Fixture::orderItem([
            'quantity' => 10,
        ])->setParent($orderItem);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 30,
        ]);
        $invoice = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 10,
        ]);
        yield [40, $childItem];

        // Adjustments
        $order           = Fixture::order();
        $orderAdjustment = Fixture::orderDiscountAdjustment(10)->setOrder($order);
        yield [0, $orderAdjustment];

        $order           = Fixture::order();
        $orderAdjustment = Fixture::orderDiscountAdjustment(10)->setOrder($order);
        $invoice         = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderAdjustment,
            'quantity' => 1,
        ]);
        yield [1, $orderAdjustment];

        // Shipment
        $order = Fixture::order();
        yield [0, $order];

        $order   = Fixture::order();
        $invoice = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $order,
            'quantity' => 1,
        ]);
        yield [1, $order];
    }

    /**
     * @param float $expected
     * @param mixed $element
     *
     * @dataProvider provide_calculateCreditedQuantity
     *
     * @TODO         Rework like test_isInvoiced
     */
    public function test_calculateCreditedQuantity(float $expected, object $element): void
    {
        $actual = $this->invoiceCalculator->calculateCreditedQuantity($element);

        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateCreditedQuantity(): \Generator
    {
        // Items
        $orderItem = Fixture::orderItem([
            'order'    => [],
            'quantity' => 10,
        ]);
        yield [0, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 10,
        ]);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderItem,
            'quantity' => 5,
        ]);
        yield [0, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 10,
        ]);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderItem,
            'quantity' => 10,
        ]);
        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        Fixture::invoiceLine([
            'invoice'  => $credit,
            'target'   => $orderItem,
            'quantity' => 5,
        ]);
        yield [5, $orderItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 2,
        ]);
        $childItem = Fixture::orderItem([
            'quantity' => 5,
        ])->setParent($orderItem);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 5,
        ]);
        yield [0, $childItem];

        $order     = Fixture::order();
        $orderItem = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 5,
        ]);
        $childItem = Fixture::orderItem([
            'quantity' => 10,
        ])->setParent($orderItem);
        $invoice   = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 30,
        ]);
        $invoice = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $childItem,
            'quantity' => 10,
        ]);
        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        Fixture::invoiceLine([
            'invoice'  => $credit,
            'target'   => $childItem,
            'quantity' => 10,
        ]);
        yield [10, $childItem];

        // Adjustments
        $order           = Fixture::order();
        $orderAdjustment = Fixture::orderDiscountAdjustment(10)->setOrder($order);
        yield [0, $orderAdjustment];

        $order           = Fixture::order();
        $orderAdjustment = Fixture::orderDiscountAdjustment(10)->setOrder($order);
        $invoice         = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $orderAdjustment,
            'quantity' => 1,
        ]);
        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        Fixture::invoiceLine([
            'invoice'  => $credit,
            'target'   => $orderAdjustment,
            'quantity' => 1,
        ]);
        yield [1, $orderAdjustment];

        // Shipment
        $order = Fixture::order();
        yield [0, $order];

        $order   = Fixture::order();
        $invoice = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $order,
            'quantity' => 1,
        ]);
        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        Fixture::invoiceLine([
            'invoice'  => $credit,
            'target'   => $order,
            'quantity' => 1,
        ]);
        yield [1, $order];
    }

    /**
     * @param array $order
     * @param array $amounts
     *
     * @dataProvider provide_calculateInvoiceTotal
     *
     * @TODO         Rework like test_isInvoiced
     */
    public function test_calculateInvoiceTotal(array $order, array $amounts): void
    {
        $order = Fixture::order($order);

        foreach ($amounts as $currency => $expected) {
            $actual = $this->invoiceCalculator->calculateInvoiceTotal($order, $currency);
            $this->assertEquals($expected, $actual);
        }
    }

    public function provide_calculateInvoiceTotal(): \Generator
    {
        yield 'Case 1' => [
            [
                'invoices' => [
                    ['grand_total' => 100],
                    ['grand_total' => 50],
                ],
            ],
            [
                Fixture::CURRENCY_EUR => 150,
                Fixture::CURRENCY_USD => 187.50,
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
                Fixture::CURRENCY_EUR => 120,
                Fixture::CURRENCY_USD => 150,
            ],
        ];

        yield 'Case 3' => [
            [
                'currency'      => Fixture::CURRENCY_USD,
                'exchange_rate' => 1.12,
                'invoices'      => [
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 100],
                    ['currency' => Fixture::CURRENCY_USD, 'grand_total' => 50],
                ],
            ],
            [
                Fixture::CURRENCY_EUR => 133.93,
                Fixture::CURRENCY_USD => 150,
            ],
        ];
    }

    /**
     * @param float  $expected
     * @param mixed  $order
     * @param string $currency
     *
     * @dataProvider provide_calculateCreditTotal
     *
     * @TODO         Rework like test_isInvoiced
     */
    public function test_calculateCreditTotal(float $expected, object $order, string $currency): void
    {
        $actual = $this->invoiceCalculator->calculateCreditTotal($order, $currency);
        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateCreditTotal(): \Generator
    {
        $order = Fixture::order();

        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        $credit->setCurrency(Fixture::CURRENCY_EUR);
        $credit->setGrandTotal(100);

        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        $credit->setCurrency(Fixture::CURRENCY_EUR);
        $credit->setGrandTotal(50);

        yield [150.00, $order, Fixture::CURRENCY_EUR];
        yield [187.50, $order, Fixture::CURRENCY_USD];

        $order = Fixture::order([
            'currency' => Fixture::CURRENCY_USD,
        ]);

        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        $credit->setCurrency(Fixture::CURRENCY_USD);
        $credit->setGrandTotal(100);

        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        $credit->setCurrency(Fixture::CURRENCY_USD);
        $credit->setGrandTotal(50);

        $invoice = Fixture::invoice(['order' => $order]);
        $invoice->setCurrency(Fixture::CURRENCY_USD);
        $invoice->setGrandTotal(50);

        yield [150.00, $order, Fixture::CURRENCY_USD];
        yield [120.00, $order, Fixture::CURRENCY_EUR];

        $order = Fixture::order([
            'currency' => Fixture::CURRENCY_USD,
        ]);
        $order->setExchangeRate(1.12);

        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        $credit->setCurrency(Fixture::CURRENCY_USD);
        $credit->setGrandTotal(100);

        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        $credit->setCurrency(Fixture::CURRENCY_USD);
        $credit->setGrandTotal(50);

        yield [133.93, $order, Fixture::CURRENCY_EUR];
        yield [150.00, $order, Fixture::CURRENCY_USD];
    }

    /**
     * @param array $expected
     * @param mixed $order
     * @param array $shipment
     *
     * @dataProvider provide_buildInvoiceQuantityMap
     *
     * @TODO         Rework like test_isInvoiced
     */
    public function test_buildInvoiceQuantityMap(array $expected, object $order, array $shipment = []): void
    {
        $this->configureShipmentCalculator($order, $shipment);

        $actual = $this->invoiceCalculator->buildInvoiceQuantityMap($order);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Configures the shipment calculator mock.
     *
     * @param OrderInterface $order
     * @param array          $map
     */
    protected function configureShipmentCalculator(OrderInterface $order, array $map): void
    {
        if (empty($map)) {
            return;
        }

        foreach ($map as $id => $data) {
            if (empty($data)) {
                continue;
            }

            $item = null;
            foreach ($order->getItems() as $i) {
                if ($i->getId() == $id) {
                    $item = $i;
                    break;
                }
            }

            if (!$item) {
                continue;
            }

            if (isset($data['shipped'])) {
                $this
                    ->shipmentCalculator
                    ->expects($this->once())
                    ->method('calculateShippedQuantity')
                    ->with($item)
                    ->willReturn($data['shipped']);
            }

            if (isset($data['returned'])) {
                $this
                    ->shipmentCalculator
                    ->expects($this->once())
                    ->method('calculateReturnedQuantity')
                    ->with($item)
                    ->willReturn($data['returned']);
            }

        }
    }

    public function provide_buildInvoiceQuantityMap(): \Generator
    {
        Fixture::clear();

        $order = Fixture::order([
            'items' => [
                [
                    'quantity' => 10.,
                ],
            ],
        ]);
        yield [
            [
                1 => [
                    'total'    => 10.,
                    'invoiced' => 0.,
                    'credited' => 0.,
                    'shipped'  => 0.,
                    'returned' => 0.,
                ],
            ],
            $order,
        ];

        Fixture::clear();

        $order = Fixture::order();

        $item0 = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 5.,
        ]);

        $item1 = Fixture::orderItem([
            'order'    => $order,
            'quantity' => 5.,
        ])->setCompound(true);

        $item2 = Fixture::orderItem([
            'quantity' => 10.,
        ])->setParent($item1);

        $invoice = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $item0,
            'quantity' => 5.,
        ]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $item2,
            'quantity' => 30.,
        ]);

        $invoice = Fixture::invoice(['order' => $order]);
        Fixture::invoiceLine([
            'invoice'  => $invoice,
            'target'   => $item2,
            'quantity' => 10.,
        ]);

        $credit = Fixture::invoice(['order' => $order, 'credit' => true]);
        Fixture::invoiceLine([
            'invoice'  => $credit,
            'target'   => $item2,
            'quantity' => 10.,
        ]);

        yield [
            [
                1 => [
                    'total'    => 5.,
                    'invoiced' => 5.,
                    'credited' => 0.,
                    'shipped'  => 0.,
                    'returned' => 0.,
                ],
                3 => [
                    'total'    => 50.,
                    'invoiced' => 40.,
                    'credited' => 10.,
                    'shipped'  => 0.,
                    'returned' => 0.,
                ],
            ],
            $order,
        ];

        Fixture::clear();

        $order = Fixture::order();

        $item0 = Fixture::orderItem([
            '_reference' => 'item0',
            'order'      => $order,
            'quantity'   => 5.,
        ]);

        Fixture::invoiceLine([
            'invoice'  => ['order' => $order],
            'target'   => $item0,
            'quantity' => 5.,
        ]);

        Fixture::invoiceLine([
            'invoice'  => ['order' => $order, 'credit' => true],
            'target'   => $item0,
            'quantity' => 2.,
        ]);

        // Credit that cancel sold quantity (not shipped)
        yield [
            [
                1 => [
                    'total'    => 5.,
                    'invoiced' => 5.,
                    'credited' => 2.,
                    'shipped'  => 3.,
                    'returned' => 0.,
                ],
            ],
            $order,
            [
                1 => [
                    'shipped' => 3.,
                ],
            ],
        ];
    }
}
