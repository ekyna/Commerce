<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Margin;
use Ekyna\Component\Commerce\Tests\TestCase;

/**
 * Class MarginTest
 * @package Ekyna\Component\Commerce\Tests\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MarginTest extends TestCase
{
    public function testGetAmount(): void
    {
        $margin = self::margin1();

        self::assertEquals(new Decimal('46.25'), $margin->getTotal(false));
        self::assertEquals(new Decimal('76.54'), $margin->getTotal(true));
    }

    public function testGetPercent(): void
    {
        $margin = self::margin1();

        self::assertEquals(new Decimal('22.51'), $margin->getPercent(false));
        self::assertEquals(new Decimal('38.27'), $margin->getPercent(true));
    }

    private static function margin1(): Margin
    {
        return new Margin(
            new Decimal('199.99'),
            new Decimal('5.50'),
            // 205.49
            new Decimal('123.45'),
            new Decimal('23.45'),
            new Decimal('12.34'),
        // 159.24
        );
    }
}
