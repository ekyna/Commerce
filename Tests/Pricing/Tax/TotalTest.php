<?php

namespace Ekyna\Component\Commerce\Tests\Pricing\Tax;

use Ekyna\Component\Commerce\Pricing\Total\Amount;
use Ekyna\Component\Commerce\Pricing\Total\AmountInterface;
use Ekyna\Component\Commerce\Pricing\Total\Amounts;
use Ekyna\Component\Commerce\Pricing\Total\AmountsInterface;

/**
 * Class TaxAmountCollectionTest
 * @package Ekyna\Component\Commerce\Tests\Pricing\Total
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @coversDefaultClass Ekyna\Component\Commerce\Pricing\Total\Total
 */
class TotalTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AmountsInterface
     */
    private $total;

    /**
     * @var AmountInterface
     */
    private $taxAmount;


    protected function setUp()
    {
        $this->total = new Amounts();

        $this->taxAmount = new Amount('TVA 20%', 20);
        $this->total->addTaxAmount($this->taxAmount);
    }

    protected function tearDown()
    {
        $this->total = null;
    }

    /**
     * @covers ::__constructor
     */
    public function testConstructor()
    {
        $total = new Amounts();

        $this->assertEquals(0, count($total->all()));
    }

    /**
     * @covers ::clear
     */
    public function testClear()
    {
        $this->total->clear();

        $this->assertEquals(0, count($this->total->all()));
    }

    /**
     * @covers ::getTaxAmounts
     */
    public function testGetTaxAmounts()
    {
        $this->assertEquals(array($this->taxAmount), $this->total->all());
    }

    /**
     * @covers ::hasTaxAmount
     */
    public function testHasTaxAmount()
    {
        $taxAmount = new Amount('TVA 20%', 20);

        $this->assertTrue($this->total->has($taxAmount));
    }

    /**
     * @covers ::addTaxAmount
     */
    public function testAddTaxAmount()
    {
        $taxAmount = new Amount('US-CA %8.25%', 8.25);

        $this->total->add($taxAmount);

        $this->assertEquals(2, count($this->total->all()));
    }

    /**
     * @covers ::removeTaxAmount
     */
    public function testRemoveTaxAmount()
    {
        $taxAmount = new Amount('TVA 20%', 20);

        $this->total->remove($taxAmount);

        $this->assertEquals(0, count($this->total->all()));
    }
}
