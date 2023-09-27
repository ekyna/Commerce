<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Invoice\Calculator;

use Ekyna\Component\Commerce\Invoice\Calculator\InvoiceMarginCalculator;
use Ekyna\Component\Commerce\Tests\Common\Model\AbstractMarginTest;
use Ekyna\Component\Commerce\Tests\Data;
use Ekyna\Component\Commerce\Tests\Fixture;
use Ekyna\Component\Commerce\Tests\Util;
use Generator;

/**
 * Class InvoiceMarginCalculatorTest
 * @package Ekyna\Component\Commerce\Tests\Invoice\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class InvoiceMarginCalculatorTest extends AbstractMarginTest
{
    private InvoiceMarginCalculator|null $calculator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calculator = new InvoiceMarginCalculator(
            $this->amountCalculatorFactory,
            $this->itemCostCalculator,
            $this->shipmentCostCalculator,
            Fixture::CURRENCY_EUR,
        );
    }

    protected function tearDown(): void
    {
        $this->calculator = null;

        parent::tearDown();
    }

    /**
     * @dataProvider provideData
     */
    public function testCalculateInvoice(string $reference, array $data): void
    {
        Fixture::order($data);

        $invoice = Fixture::get($reference);

        $actual = $this->calculator->calculateInvoice($invoice);

        $this->assertMargin($actual, Util::margin([]));
    }

    public function provideData(): Generator
    {
        yield 'Invoice 1' => [
            'order1_invoice1',
            Data::order1(),
            [

            ],
        ];
    }

    private function configureAmountCalculator(array $data): void
    {

    }
}
