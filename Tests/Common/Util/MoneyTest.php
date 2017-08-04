<?php

namespace Ekyna\Component\Commerce\Tests\Common\Util;

use PHPUnit\Framework\TestCase;
use Ekyna\Component\Commerce\Common\Util\Money;

/**
 * Class MoneyTest
 * @package Ekyna\Component\Commerce\Tests\Common\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MoneyTest extends TestCase
{
    public function testRound()
    {
        $this->assertEquals(12.34, Money::round(12.3412, 'EUR'));
        $this->assertEquals(12.35, Money::round(12.3456, 'EUR'));
    }

    public function testCompare()
    {
        $this->assertEquals(0, Money::compare(12.3456, 12.3412, 'EUR'));
        $this->assertEquals(1, Money::compare(12.35, 12.34, 'EUR'));
        $this->assertEquals(1, Money::compare(12.35, 12.34, 'EUR'));
        $this->assertEquals(-1, Money::compare(12.34, 12.35, 'EUR'));
    }
}
