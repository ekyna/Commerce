<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Subject\Model;

/**
 * Class PurchaseCost
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PurchaseCost
{
    /** @var float */
    private $good;

    /** @var float */
    private $shipping;


    public function __construct(float $good, float $shipping)
    {
        $this->good = $good;
        $this->shipping = $shipping;
    }

    public function getGood(): float
    {
        return $this->good;
    }

    public function getShipping(): float
    {
        return $this->shipping;
    }
}
