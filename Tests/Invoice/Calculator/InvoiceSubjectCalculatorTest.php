<?php

namespace Ekyna\Component\Commerce\Tests\Invoice\Calculator;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceSubjectCalculator;
use Ekyna\Component\Commerce\Tests\Fixtures\Fixtures;
use Ekyna\Component\Commerce\Tests\TestCase;

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

    protected function setUp(): void
    {
        $this->invoiceCalculator = new InvoiceSubjectCalculator($this->getCurrencyConverter());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->invoiceCalculator = null;
    }

    /**
     * @param bool   $expected
     * @param mixed $element
     *
     * @dataProvider provide_isInvoiced
     */
    public function test_isInvoiced(bool $expected, $element): void
    {
        $actual = $this->invoiceCalculator->isInvoiced($element);

        $this->assertEquals($expected, $actual);
    }

    public function provide_isInvoiced(): \Generator
    {
        // Item not invoiced
        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(5)->setOrder($order);
        yield [false, $orderItem];

        // Item invoiced
        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(5)->setOrder($order);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderItem)->setQuantity(5);
        yield [true, $orderItem];

        // Child item not invoiced
        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(5)->setOrder($order);
        $childItem = Fixtures::createOrderItem(10)->setParent($orderItem);
        yield [false, $childItem];

        // Child item invoiced
        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(5)->setOrder($order);
        $childItem = Fixtures::createOrderItem(10)->setParent($orderItem);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(5);
        yield [true, $childItem];

        // Adjustment not invoiced
        $order           = Fixtures::createOrder();
        $orderAdjustment = Fixtures::createOrderDiscountAdjustment(10)->setOrder($order);
        yield [false, $orderAdjustment];

        // Adjustment invoiced
        $order           = Fixtures::createOrder();
        $orderAdjustment = Fixtures::createOrderDiscountAdjustment(10)->setOrder($order);
        $invoice         = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderAdjustment)->setQuantity(1);
        yield [true, $orderAdjustment];
    }

    /**
     * @param float $expected
     * @param mixed $element
     *
     * @dataProvider provide_calculateInvoiceableQuantity
     */
    public function test_calculateInvoiceableQuantity(float $expected, object $element): void
    {
        $actual = $this->invoiceCalculator->calculateInvoiceableQuantity($element);

        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateInvoiceableQuantity(): \Generator
    {
        // Items
        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        yield [10, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderItem)->setQuantity(5);
        yield [5, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderItem)->setQuantity(10);
        yield [0, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(2)->setOrder($order);
        $childItem = Fixtures::createOrderItem(5)->setParent($orderItem);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(5);
        yield [5, $childItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(5)->setOrder($order);
        $childItem = Fixtures::createOrderItem(10)->setParent($orderItem);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(30);
        $invoice = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(10);
        yield [10, $childItem];

        // Adjustments
        $order           = Fixtures::createOrder();
        $orderAdjustment = Fixtures::createOrderDiscountAdjustment(10)->setOrder($order);
        yield [1, $orderAdjustment];

        $order           = Fixtures::createOrder();
        $orderAdjustment = Fixtures::createOrderDiscountAdjustment(10)->setOrder($order);
        $invoice         = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderAdjustment)->setQuantity(1);
        yield [1, $orderAdjustment];

        // Shipment
        $order = Fixtures::createOrder();
        yield [1, $order];

        $order   = Fixtures::createOrder();
        $invoice = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $order)->setQuantity(1);
        yield [0, $order];
    }

    /**
     * @param float $expected
     * @param mixed $element
     *
     * @dataProvider provide_calculateInvoicedQuantity
     */
    public function test_calculateInvoicedQuantity(float $expected, object $element): void
    {
        $actual = $this->invoiceCalculator->calculateInvoicedQuantity($element);

        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateInvoicedQuantity(): \Generator
    {
        // Items
        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        yield [0, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderItem)->setQuantity(5);
        yield [5, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderItem)->setQuantity(10);
        yield [10, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(2)->setOrder($order);
        $childItem = Fixtures::createOrderItem(5)->setParent($orderItem);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(5);
        yield [5, $childItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(5)->setOrder($order);
        $childItem = Fixtures::createOrderItem(10)->setParent($orderItem);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(30);
        $invoice = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(10);
        yield [40, $childItem];

        // Adjustments
        $order           = Fixtures::createOrder();
        $orderAdjustment = Fixtures::createOrderDiscountAdjustment(10)->setOrder($order);
        yield [0, $orderAdjustment];

        $order           = Fixtures::createOrder();
        $orderAdjustment = Fixtures::createOrderDiscountAdjustment(10)->setOrder($order);
        $invoice         = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderAdjustment)->setQuantity(1);
        yield [1, $orderAdjustment];

        // Shipment
        $order = Fixtures::createOrder();
        yield [0, $order];

        $order   = Fixtures::createOrder();
        $invoice = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $order)->setQuantity(1);
        yield [1, $order];
    }

    /**
     * @param float $expected
     * @param mixed $element
     *
     * @dataProvider provide_calculateCreditedQuantity
     */
    public function test_calculateCreditedQuantity(float $expected, object $element): void
    {
        $actual = $this->invoiceCalculator->calculateCreditedQuantity($element);

        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateCreditedQuantity(): \Generator
    {
        // Items
        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        yield [0, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderItem)->setQuantity(5);
        yield [0, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(10)->setOrder($order);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderItem)->setQuantity(10);
        $credit = Fixtures::createInvoice($order, true);
        Fixtures::createInvoiceLine($credit, $orderItem)->setQuantity(5);
        yield [5, $orderItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(2)->setOrder($order);
        $childItem = Fixtures::createOrderItem(5)->setParent($orderItem);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(5);
        yield [0, $childItem];

        $order     = Fixtures::createOrder();
        $orderItem = Fixtures::createOrderItem(5)->setOrder($order);
        $childItem = Fixtures::createOrderItem(10)->setParent($orderItem);
        $invoice   = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(30);
        $invoice = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $childItem)->setQuantity(10);
        $credit = Fixtures::createInvoice($order, true);
        Fixtures::createInvoiceLine($credit, $childItem)->setQuantity(10);
        yield [10, $childItem];

        // Adjustments
        $order           = Fixtures::createOrder();
        $orderAdjustment = Fixtures::createOrderDiscountAdjustment(10)->setOrder($order);
        yield [0, $orderAdjustment];

        $order           = Fixtures::createOrder();
        $orderAdjustment = Fixtures::createOrderDiscountAdjustment(10)->setOrder($order);
        $invoice         = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $orderAdjustment)->setQuantity(1);
        $credit = Fixtures::createInvoice($order, true);
        Fixtures::createInvoiceLine($credit, $orderAdjustment)->setQuantity(1);
        yield [1, $orderAdjustment];

        // Shipment
        $order = Fixtures::createOrder();
        yield [0, $order];

        $order   = Fixtures::createOrder();
        $invoice = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $order)->setQuantity(1);
        $credit = Fixtures::createInvoice($order, true);
        Fixtures::createInvoiceLine($credit, $order)->setQuantity(1);
        yield [1, $order];
    }

    /**
     * @param float  $expected
     * @param mixed  $order
     * @param string $currency
     *
     * @dataProvider provide_calculateInvoiceTotal
     */
    public function test_calculateInvoiceTotal(float $expected, object $order, string $currency): void
    {
        $actual = $this->invoiceCalculator->calculateInvoiceTotal($order, $currency);
        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateInvoiceTotal(): \Generator
    {
        $order = Fixtures::createOrder('EUR');

        $invoice = Fixtures::createInvoice($order);
        $invoice->setCurrency('EUR');
        $invoice->setGrandTotal(100);

        $invoice = Fixtures::createInvoice($order);
        $invoice->setCurrency('EUR');
        $invoice->setGrandTotal(50);

        yield [150.00, $order, 'EUR'];
        yield [187.50, $order, 'USD'];

        $order = Fixtures::createOrder('USD');

        $invoice = Fixtures::createInvoice($order);
        $invoice->setCurrency('USD');
        $invoice->setGrandTotal(100);

        $invoice = Fixtures::createInvoice($order);
        $invoice->setCurrency('USD');
        $invoice->setGrandTotal(50);

        $credit = Fixtures::createInvoice($order, true);
        $credit->setCurrency('USD');
        $credit->setGrandTotal(50);

        yield [150.00, $order, 'USD'];
        yield [120.00, $order, 'EUR'];

        $order = Fixtures::createOrder('USD');
        $order->setExchangeRate(1.12);

        $invoice = Fixtures::createInvoice($order);
        $invoice->setCurrency('USD');
        $invoice->setGrandTotal(100);

        $invoice = Fixtures::createInvoice($order);
        $invoice->setCurrency('USD');
        $invoice->setGrandTotal(50);

        yield [133.93, $order, 'EUR'];
        yield [150.00, $order, 'USD'];
    }

    /**
     * @param float  $expected
     * @param mixed  $order
     * @param string $currency
     *
     * @dataProvider provide_calculateCreditTotal
     */
    public function test_calculateCreditTotal(float $expected, object $order, string $currency): void
    {
        $actual = $this->invoiceCalculator->calculateCreditTotal($order, $currency);
        $this->assertEquals($expected, $actual);
    }

    public function provide_calculateCreditTotal(): \Generator
    {
        $order = Fixtures::createOrder('EUR');

        $credit = Fixtures::createInvoice($order, true);
        $credit->setCurrency('EUR');
        $credit->setGrandTotal(100);

        $credit = Fixtures::createInvoice($order, true);
        $credit->setCurrency('EUR');
        $credit->setGrandTotal(50);

        yield [150.00, $order, 'EUR'];
        yield [187.50, $order, 'USD'];

        $order = Fixtures::createOrder('USD');

        $credit = Fixtures::createInvoice($order, true);
        $credit->setCurrency('USD');
        $credit->setGrandTotal(100);

        $credit = Fixtures::createInvoice($order, true);
        $credit->setCurrency('USD');
        $credit->setGrandTotal(50);

        $invoice = Fixtures::createInvoice($order);
        $invoice->setCurrency('USD');
        $invoice->setGrandTotal(50);

        yield [150.00, $order, 'USD'];
        yield [120.00, $order, 'EUR'];

        $order = Fixtures::createOrder('USD');
        $order->setExchangeRate(1.12);

        $credit = Fixtures::createInvoice($order, true);
        $credit->setCurrency('USD');
        $credit->setGrandTotal(100);

        $credit = Fixtures::createInvoice($order, true);
        $credit->setCurrency('USD');
        $credit->setGrandTotal(50);

        yield [133.93, $order, 'EUR'];
        yield [150.00, $order, 'USD'];
    }

    /**
     * @param array $expected
     * @param mixed $order
     *
     * @dataProvider provide_buildInvoiceQuantityMap
     */
    public function test_buildInvoiceQuantityMap(array $expected, object $order): void
    {
        $actual = $this->invoiceCalculator->buildInvoiceQuantityMap($order);

        $this->assertEquals($expected, $actual);
    }

    public function provide_buildInvoiceQuantityMap(): \Generator
    {
        $order = Fixtures::createOrder();
        $item1 = Fixtures::createOrderItem(10)->setOrder($order);
        Fixtures::setId($item1, 1);
        yield [
            [
                1 => [
                    'total'    => 10,
                    'invoiced' => 0,
                    'credited' => 0,
                ],
            ],
            $order,
        ];

        $order = Fixtures::createOrder();

        $item0 = Fixtures::createOrderItem(5)->setOrder($order);
        Fixtures::setId($item0, 1);

        $item1 = Fixtures::createOrderItem(5)->setOrder($order)->setCompound(true);
        Fixtures::setId($item1, 2);

        $item2 = Fixtures::createOrderItem(10)->setParent($item1);
        Fixtures::setId($item2, 3);

        $invoice = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $item0)->setQuantity(5);
        Fixtures::createInvoiceLine($invoice, $item2)->setQuantity(30);

        $invoice = Fixtures::createInvoice($order);
        Fixtures::createInvoiceLine($invoice, $item2)->setQuantity(10);

        $credit = Fixtures::createInvoice($order, true);
        Fixtures::createInvoiceLine($credit, $item2)->setQuantity(10);

        yield [
            [
                1 => [
                    'total'    => 5,
                    'invoiced' => 5,
                    'credited' => 0,
                ],
                3 => [
                    'total'    => 50,
                    'invoiced' => 40,
                    'credited' => 10,
                ],
            ],
            $order,
        ];
    }
}
