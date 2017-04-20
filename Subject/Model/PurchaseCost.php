<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Model;

use Decimal\Decimal;

/**
 * Class PurchaseCost
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PurchaseCost
{
    private Decimal $good;
    private Decimal $shipping;

    public function __construct(Decimal $good, Decimal $shipping)
    {
        $this->good = $good;
        $this->shipping = $shipping;
    }

    public function getGood(): Decimal
    {
        return $this->good;
    }

    public function getShipping(): Decimal
    {
        return $this->shipping;
    }
}
