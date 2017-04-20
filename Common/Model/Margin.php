<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Util\Money;

/**
 * Class Margin
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Margin
{
    private string $currency;
    private Decimal $purchaseCost;
    private Decimal $sellingPrice;
    private bool $average;


    public function __construct(string $currency, Decimal $cost = null, Decimal $price = null, bool $average = false)
    {
        $this->currency = $currency;
        $this->purchaseCost = $cost ?: new Decimal(0);
        $this->sellingPrice = $price ?: new Decimal(0);
        $this->average = $average;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function addPurchaseCost(Decimal $cost): Margin
    {
        $this->purchaseCost += $cost;

        return $this;
    }

    public function addSellingPrice(Decimal $price): Margin
    {
        $this->sellingPrice += $price;

        return $this;
    }

    public function getPercent(): Decimal
    {
        $amount = $this->getAmount();

        if (0 < $this->sellingPrice) {
            return $amount->mul(100)->div($this->sellingPrice)->round(2);
        }

        return new Decimal(0);
    }

    public function getAmount(): Decimal
    {
        return Money::round($this->sellingPrice - $this->purchaseCost, $this->currency);
    }

    public function merge(Margin $margin): void
    {
        $this->purchaseCost += $margin->getPurchaseCost();
        $this->sellingPrice += $margin->getSellingPrice();
        $this->average = $this->average || $margin->isAverage();
    }

    public function getPurchaseCost(): Decimal
    {
        return $this->purchaseCost;
    }

    public function getSellingPrice(): Decimal
    {
        return $this->sellingPrice;
    }

    public function isAverage(): bool
    {
        return $this->average;
    }

    public function setAverage(bool $average): Margin
    {
        $this->average = $average;

        return $this;
    }
}
