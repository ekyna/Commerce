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
    /**
     * @dataProvider provide_round
     */
    public function test_round($value, $currency, $result): void
    {
        $this->assertEquals($result, Money::round($value, $currency));
    }

    public function provide_round(): array
    {
        return [
            'Case 1' => [12.345, 'EUR', 12.34],
            'Case 2' => [12.3450, 'EUR', 12.34],
            'Case 3' => [12.3451, 'EUR', 12.35],
            'Case 4' => [12.3458, 'EUR', 12.35],
            // TODO Swiss
        ];
    }

    /**
     * @dataProvider provide_compare
     */
    public function test_compare($a, $b, $currency, $result): void
    {
        $this->assertEquals($result, Money::compare($a, $b, $currency));
    }

    public function provide_compare(): array
    {
        return [
            'Case 1' => [12.34, 12.3450, 'EUR', 0],
            'Case 2' => [12.35, 12.3450, 'EUR', 1],
            'Case 3' => [12.3456, 12.3412, 'EUR', 1],
            'Case 4' => [12.3412, 12.3456, 'EUR', -1],
            'Case 5' => [186.3799, 186.38, 'GBP', 0],
            // TODO
        ];
    }
}
