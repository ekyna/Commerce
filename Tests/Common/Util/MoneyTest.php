<?php

namespace Ekyna\Component\Commerce\Tests\Common\Util;

use Ekyna\Component\Commerce\Common\Util\Money;
use Ekyna\Component\Commerce\Tests\Fixture;
use PHPUnit\Framework\TestCase;

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
            'Case 1' => [12.345, Fixture::CURRENCY_EUR, 12.34],
            'Case 2' => [12.3450, Fixture::CURRENCY_EUR, 12.34],
            'Case 3' => [12.3451, Fixture::CURRENCY_EUR, 12.35],
            'Case 4' => [12.3458, Fixture::CURRENCY_EUR, 12.35],
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
            'Case 1' => [12.34, 12.3450, Fixture::CURRENCY_EUR, 0],
            'Case 2' => [12.35, 12.3450, Fixture::CURRENCY_EUR, 1],
            'Case 3' => [12.3456, 12.3412, Fixture::CURRENCY_EUR, 1],
            'Case 4' => [12.3412, 12.3456, Fixture::CURRENCY_EUR, -1],
            'Case 5' => [186.3799, 186.38, Fixture::CURRENCY_USD, 0],
            // TODO
        ];
    }
}
