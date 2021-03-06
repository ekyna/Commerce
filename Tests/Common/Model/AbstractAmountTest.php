<?php

namespace Ekyna\Component\Commerce\Tests\Common\Model;

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
     *
     * @param Amount $result
     * @param float  $unit
     * @param float  $gross
     * @param float  $discount
     * @param float  $base
     * @param float  $tax
     * @param float  $total
     */
    protected function assertResult(
        Amount $result,
        float $unit,
        float $gross,
        float $discount,
        float $base,
        float $tax,
        float $total
    ) {
        $this->assertEquals($unit, $result->getUnit());
        $this->assertEquals($gross, $result->getGross());
        $this->assertEquals($discount, $result->getDiscount());
        $this->assertEquals($base, $result->getBase());
        $this->assertEquals($tax, $result->getTax());
        $this->assertEquals($total, $result->getTotal());
    }

    /**
     * Makes assertions on the given result adjustment's amounts.
     *
     * @param Adjustment $adjustment
     * @param string     $name
     * @param float      $amount
     * @param float      $rate
     */
    protected function assertAdjustment(Adjustment $adjustment, $name, $amount, $rate)
    {
        $this->assertEquals($name, $adjustment->getName());
        $this->assertEquals($amount, $adjustment->getAmount());
        $this->assertEquals($rate, $adjustment->getRate());
    }
}
