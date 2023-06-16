<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;

/**
 * Class Margin
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Margin
{
    public function __construct(
        private Decimal $revenueProduct = new Decimal(0),
        private Decimal $revenueShipment = new Decimal(0),
        private Decimal $costProduct = new Decimal(0),
        private Decimal $costSupply = new Decimal(0),
        private Decimal $costShipment = new Decimal(0),
        private bool    $average = false,
    ) {
    }

    public function __clone(): void
    {
        $this->revenueProduct = clone $this->revenueProduct;
        $this->revenueShipment = clone $this->revenueShipment;
        $this->costProduct = clone $this->costProduct;
        $this->costSupply = clone $this->costSupply;
        $this->costShipment = clone $this->costShipment;
    }

    public function addRevenue(Revenue $revenue): Margin
    {
        $this->revenueProduct += $revenue->getProduct();
        $this->revenueShipment += $revenue->getShipment();

        return $this;
    }

    public function addCost(Cost $cost): Margin
    {
        $this->costProduct += $cost->getProduct();
        $this->costSupply += $cost->getSupply();
        $this->costShipment += $cost->getShipment();

        if ($cost->isAverage()) {
            $this->average = true;
        }

        return $this;
    }

    /**
     * Returns this revenue total.
     *
     * @param bool $gross Whether to include shipment revenue.
     * @return Decimal
     */
    public function getRevenueTotal(bool $gross): Decimal
    {
        if ($gross) {
            return $this->revenueProduct;
        }

        return $this
            ->revenueProduct
            ->add($this->revenueShipment);
    }

    /**
     * Returns the cost total.
     *
     * @param bool $gross Whether to include supply and shipment costs.
     * @return Decimal
     */
    public function getCostTotal(bool $gross): Decimal
    {
        if ($gross) {
            return $this->costProduct;
        }

        return $this
            ->costProduct
            ->add($this->costSupply)
            ->add($this->costShipment);
    }

    public function getPercent(bool $gross): Decimal
    {
        if (0 < $revenue = $this->getRevenueTotal($gross)) {
            // (1 - (cost / revenue) ) * 100
            return (new Decimal(1))->sub(
                $this->getCostTotal($gross)->div($revenue)
            )->mul(100)->round(2);
        }

        return new Decimal(0);
    }

    public function getTotal(bool $gross): Decimal
    {
        return $this->getRevenueTotal($gross) - $this->getCostTotal($gross);
    }

    /**
     * Merges the other margin into this one.
     *
     * @param Margin $other
     */
    public function merge(Margin $other): void
    {
        $this->revenueProduct += $other->revenueProduct;
        $this->revenueShipment += $other->revenueShipment;

        $this->costProduct += $other->costProduct;
        $this->costSupply += $other->costSupply;
        $this->costShipment += $other->costShipment;

        if ($other->average) {
            $this->average = true;
        }
    }

    /**
     * Multiplies this margin.
     *
     * @param Decimal $quantity
     */
    public function multiply(Decimal $quantity): void
    {
        $this->revenueProduct *= $quantity;
        $this->revenueShipment *= $quantity;

        $this->costProduct *= $quantity;
        $this->costSupply *= $quantity;
        $this->costShipment *= $quantity;
    }

    /**
     * Negates this margin.
     */
    public function negate(): void
    {
        $this->revenueProduct = $this->revenueProduct->negate();
        $this->revenueShipment = $this->revenueShipment->negate();

        $this->costProduct = $this->costProduct->negate();
        $this->costSupply = $this->costSupply->negate();
        $this->costShipment = $this->costShipment->negate();
    }

    public function equals(Margin $other): bool
    {
        return $this->revenueProduct->equals($other->revenueProduct)
            && $this->revenueShipment->equals($other->revenueShipment)
            && $this->costProduct->equals($other->costProduct)
            && $this->costSupply->equals($other->costSupply)
            && $this->costShipment->equals($other->costShipment)
            && $this->average === $other->average;
    }

    public function addRevenueProduct(Decimal $value): Margin
    {
        $this->revenueProduct += $value;

        return $this;
    }

    public function addRevenueShipment(Decimal $value): Margin
    {
        $this->revenueShipment += $value;

        return $this;
    }

    public function addCostProduct(Decimal $value): Margin
    {
        $this->costProduct += $value;

        return $this;
    }

    public function addCostSupply(Decimal $value): Margin
    {
        $this->costSupply += $value;

        return $this;
    }

    public function addCostShipment(Decimal $value): Margin
    {
        $this->costShipment += $value;

        return $this;
    }

    public function getRevenueProduct(): Decimal
    {
        return $this->revenueProduct;
    }

    public function getRevenueShipment(): Decimal
    {
        return $this->revenueShipment;
    }

    public function getCostProduct(): Decimal
    {
        return $this->costProduct;
    }

    public function getCostSupply(): Decimal
    {
        return $this->costSupply;
    }

    public function getCostShipment(): Decimal
    {
        return $this->costShipment;
    }

    public function isAverage(): bool
    {
        return $this->average;
    }
}
