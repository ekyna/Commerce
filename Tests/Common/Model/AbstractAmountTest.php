<?php

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
        float $unit,
        float $gross,
        float $discount,
        float $base,
        float $tax,
        float $total
    ): void {
        $this->assertEquals(new Decimal((string)$unit), $result->getUnit());
        $this->assertEquals(new Decimal((string)$gross), $result->getGross());
        $this->assertEquals(new Decimal((string)$discount), $result->getDiscount());
        $this->assertEquals(new Decimal((string)$base), $result->getBase());
        $this->assertEquals(new Decimal((string)$tax), $result->getTax());
        $this->assertEquals(new Decimal((string)$total), $result->getTotal());
    }

    /**
     * Makes assertions on the given result adjustment's amounts.
     */
    protected function assertAdjustment(Adjustment $adjustment, string $name, float $amount, float $rate)
    {
        $this->assertEquals($name, $adjustment->getName());
        $this->assertEquals(new Decimal((string)$amount), $adjustment->getAmount());
        $this->assertEquals(new Decimal((string)$rate), $adjustment->getRate());
    }
}
