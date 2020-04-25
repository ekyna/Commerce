<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Commerce\Common\Util\Money;

/**
 * Class Margin
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Margin
{
    /**
     * @var string
     */
    private $currency;

    /**
     * @var float
     */
    private $purchaseCost;

    /**
     * @var float
     */
    private $sellingPrice;

    /**
     * @var bool
     */
    private $average;


    /**
     * Constructor.
     *
     * @param string $currency
     * @param float  $cost
     * @param float  $price
     * @param bool   $average
     */
    public function __construct(string $currency, float $cost = 0., float $price = 0., bool $average = false)
    {
        $this->currency = $currency;
        $this->purchaseCost = $cost;
        $this->sellingPrice = $price;
        $this->average = $average;
    }

    /**
     * Returns the currency.
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Adds the purchase cost.
     *
     * @param float $cost
     *
     * @return Margin
     */
    public function addPurchaseCost(float $cost): Margin
    {
        $this->purchaseCost += $cost;

        return $this;
    }

    /**
     * Adds the selling price
     *
     * @param float $price
     *
     * @return Margin
     */
    public function addSellingPrice(float $price): Margin
    {
        $this->sellingPrice += $price;

        return $this;
    }

    /**
     * Returns the percentage.
     *
     * @return float
     */
    public function getPercent(): float
    {
        $amount = $this->getAmount();

        if (0 < $this->sellingPrice) {
            return round($amount * 100 / $this->sellingPrice, 2);
        }

        return 0;
    }

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount(): float
    {
        return Money::round($this->sellingPrice - $this->purchaseCost, $this->currency);
    }

    /**
     * Merges the given margin.
     *
     * @param Margin $margin
     */
    public function merge(Margin $margin): void
    {
        $this->purchaseCost += $margin->getPurchaseCost();
        $this->sellingPrice += $margin->getSellingPrice();
        $this->average = $this->average || $margin->isAverage();
    }

    /**
     * Returns the purchase cost.
     *
     * @return float
     */
    public function getPurchaseCost(): float
    {
        return $this->purchaseCost;
    }

    /**
     * Returns the selling price.
     *
     * @return float
     */
    public function getSellingPrice(): float
    {
        return $this->sellingPrice;
    }

    /**
     * Returns whether this is an average margin.
     *
     * @return bool
     */
    public function isAverage(): bool
    {
        return $this->average;
    }

    /**
     * Sets whether this is an average margin.
     *
     * @param bool $average
     *
     * @return Margin
     */
    public function setAverage(bool $average): Margin
    {
        $this->average = $average;

        return $this;
    }
}
