<?php declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

/**
 * Class Margin
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Margin
{
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
     * @param float $cost
     * @param float $price
     * @param bool  $average
     */
    public function __construct(float $cost = 0, float $price = 0, bool $average = false)
    {
        $this->purchaseCost = $cost;
        $this->sellingPrice = $price;
        $this->average = $average;
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
     * Returns the selling price.
     *
     * @return float
     */
    public function getSellingPrice(): float
    {
        return $this->sellingPrice;
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

    /**
     * Returns the amount.
     *
     * @return float
     */
    public function getAmount():float
    {
        return $this->sellingPrice - $this->purchaseCost;
    }

    /**
     * Returns the percentage.
     *
     * @return float
     */
    public function getPercent(): float
    {
        $amount = $this->getAmount();

        return round($amount * 100 / $this->sellingPrice, 2);
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
}
