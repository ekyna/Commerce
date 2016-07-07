<?php

namespace Ekyna\Component\Commerce\Tests\Pricing\Tax;

use Ekyna\Component\Commerce\Pricing\Total\Amount;

/**
 * Class TaxAmountTest
 * @package Ekyna\Component\Commerce\Tests\Pricing\Total
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @coversDefaultClass Ekyna\Component\Commerce\Pricing\Total\TaxAmount
 */
class TaxAmountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::__constructor
     */
    public function testConstructor()
    {
        $taxAmount = new Amount('TVA 20%', 20);

        $this->assertEquals('TVA 20%', $taxAmount->getTaxName());
        $this->assertEquals(20, $taxAmount->getTaxRate());
        $this->assertEquals(0, $taxAmount->getAmount());
    }

    /**
     * @covers ::getName
     */
    public function testGetName()
    {
        $taxAmount = new Amount('US-CA 8.25%', 8.25);

        $this->assertEquals('US-CA 8.25%', $taxAmount->getTaxName());
    }

    /**
     * @covers ::getRate
     */
    public function testGetRate()
    {
        $taxAmount = new Amount('US-CA 8.25%', 8.25);

        $this->assertEquals(8.25, $taxAmount->getTaxRate());
    }

    /**
     * @covers ::getBase
     */
    public function testGetBase()
    {
        $taxAmount = new Amount('US-CA 8.25%', 8.25);

        $this->assertEquals(0, $taxAmount->getAmount());
    }

    /**
     * @covers ::addBase
     */
    public function testAddBase()
    {
        $taxAmount = new Amount('US-CA 8.25%', 8.25);

        $taxAmount->addAmount(9.99);
        $this->assertEquals(9.99, $taxAmount->getAmount());

        $taxAmount->addAmount(9.99);
        $this->assertEquals(19.98, $taxAmount->getAmount());
    }

    /**
     * @covers ::equals
     */
    public function testEquals()
    {
        $taxAmountA = new Amount('US-CA 8.25%', 8.25);

        $taxAmountB = new Amount('US-CA 8.25%', 8.25);
        $this->assertTrue($taxAmountA->equals($taxAmountB));

        $taxAmountC = new Amount('TVA 20%', 8.25);
        $this->assertFalse($taxAmountA->equals($taxAmountC));

        $taxAmountD = new Amount('US-CA 8.25%', 8);
        $this->assertFalse($taxAmountA->equals($taxAmountD));
    }
}
