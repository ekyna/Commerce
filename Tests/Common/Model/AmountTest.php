<?php

namespace Ekyna\Component\Commerce\Tests\Common\Model;

use Ekyna\Component\Commerce\Common\Model\Adjustment;
use Ekyna\Component\Commerce\Common\Model\Amount;

/**
 * Class AmountTest
 * @package Ekyna\Component\Commerce\Tests\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AmountTest extends AbstractAmountTest
{
    /**
     * @covers Amount::merge()
     */
    public function test_merge()
    {
        $a = new Amount(
            'USD', 12.34, 185.1, 9.26, 175.84, 9.67, 185.51,
            [new Adjustment('Discount 5%', 6.84, 5)],
            [new Adjustment('Tax 5.5%', 9.67, 5.5)]
        );
        $b = new Amount(
            'USD', 47.99, 1151.76, 115.18, 1036.58, 57.01, 1093.59,
            [new Adjustment('Discount 10%', 115.18, 10)],
            [new Adjustment('Tax 5.5%', 57.01, 5.5)]
        );

        $c = new Amount();
        $c->merge($a, $b);

        $this->assertResult($c, 60.33, 1336.86, 124.44, 1212.42, 66.68, 1279.10);

        $discounts = $c->getDiscountAdjustments();
        $this->assertCount(2, $discounts);
        $this->assertAdjustment($discounts[0], 'Discount 5%', 6.84, 5);
        $this->assertAdjustment($discounts[1], 'Discount 10%', 115.18, 10);

        $taxes = $c->getTaxAdjustments();
        $this->assertCount(1, $taxes);
        $this->assertAdjustment($taxes[0], 'Tax 5.5%', 66.68, 5.5);
    }
}
