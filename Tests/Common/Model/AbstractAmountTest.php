<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Tests\Common\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Adjustment;
use Ekyna\Component\Commerce\Common\Model\Amount;
use Ekyna\Component\Commerce\Tests\TestCase;

/**
 * Class AbstractAmountTest
 * @package Ekyna\Component\Commerce\Tests\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAmountTest extends TestCase
{
    /**
     * Makes assertions on the given result's amounts.
     */
    protected function assertResult(
        Amount $result,
        float  $unit,
        float  $gross,
        float  $discount,
        float  $base,
        float  $tax,
        float  $total
    ): void {
        self::assertEquals(new Decimal((string)$unit), $result->getUnit());
        self::assertEquals(new Decimal((string)$gross), $result->getGross());
        self::assertEquals(new Decimal((string)$discount), $result->getDiscount());
        self::assertEquals(new Decimal((string)$base), $result->getBase());
        self::assertEquals(new Decimal((string)$tax), $result->getTax());
        self::assertEquals(new Decimal((string)$total), $result->getTotal());
    }

    /**
     * Makes assertions on the given result adjustment's amounts.
     */
    protected function assertAdjustment(Adjustment $adjustment, string $name, float $amount, float $rate): void
    {
        self::assertEquals($name, $adjustment->getName());
        self::assertEquals(new Decimal((string)$amount), $adjustment->getAmount());
        self::assertEquals(new Decimal((string)$rate), $adjustment->getRate());
    }
}
