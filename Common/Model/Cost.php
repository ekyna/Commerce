<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;
use Doctrine\Common\Comparable;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class Cost
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class Cost implements Comparable
{
    public function __construct(
        private Decimal $product = new Decimal(0),
        private Decimal $supply = new Decimal(0),
        private Decimal $shipment = new Decimal(0),
        private bool    $average = false,
    ) {
    }

    public function __clone(): void
    {
        $this->product = clone $this->product;
        $this->supply = clone $this->supply;
        $this->shipment = clone $this->shipment;
    }

    /**
     * Returns the product cost.
     *
     * @return Decimal
     */
    public function getProduct(): Decimal
    {
        return $this->product;
    }

    /**
     * Adds the given amount to product cost.
     *
     * @param Decimal $amount
     * @return Cost
     */
    public function addProduct(Decimal $amount): Cost
    {
        $result = clone $this;

        $result->product += $amount;

        return $result;
    }

    /**
     * Returns the supply cost.
     *
     * @return Decimal
     */
    public function getSupply(): Decimal
    {
        return $this->supply;
    }

    /**
     * Adds the given amount to supply cost.
     *
     * @param Decimal $amount
     * @return Cost
     */
    public function addSupply(Decimal $amount): Cost
    {
        $result = clone $this;

        $result->supply += $amount;

        return $result;
    }

    /**
     * Returns the shipment cost.
     *
     * @return Decimal
     */
    public function getShipment(): Decimal
    {
        return $this->shipment;
    }

    /**
     * Adds the given amount to shipment cost.
     *
     * @param Decimal $amount
     * @return Cost
     */
    public function addShipment(Decimal $amount): Cost
    {
        $result = clone $this;

        $result->shipment += $amount;

        return $result;
    }

    /**
     * Returns whether this cost is average.
     *
     * @return bool
     */
    public function isAverage(): bool
    {
        return $this->average;
    }

    /**
     * Sets whether this cost is average.
     *
     * @return Cost
     */
    public function setAverage(): Cost
    {
        $result = clone $this;

        $result->average = true;

        return $result;
    }

    /**
     * Returns this cost total.
     *
     * @param bool $gross Whether to include supply and shipment costs.
     * @return Decimal
     */
    public function getTotal(bool $gross): Decimal
    {
        if ($gross) {
            return $this->product;
        }

        return $this->product->add($this->supply)->add($this->shipment);
    }

    /**
     * Adds the given costs to this one.
     *
     * @param Cost $cost
     * @return Cost
     */
    public function add(Cost $cost): Cost
    {
        $result = clone $this;

        $result->product += $cost->product;
        $result->supply += $cost->supply;
        $result->shipment += $cost->shipment;

        if ($cost->isAverage()) {
            $result->average = true;
        }

        return $result;
    }

    /**
     * Multiplies this cost.
     *
     * @param Decimal $quantity
     * @return Cost
     */
    public function multiply(Decimal $quantity): Cost
    {
        $result = clone $this;

        $result->product *= $quantity;
        $result->supply *= $quantity;
        $result->shipment *= $quantity;

        return $result;
    }

    /**
     * Divides this cost.
     *
     * @param Decimal $quantity
     * @return Cost
     */
    public function divide(Decimal $quantity): Cost
    {
        $result = clone $this;

        $result->product /= $quantity;
        $result->supply /= $quantity;
        $result->shipment /= $quantity;

        return $result;
    }

    /**
     * Negates the costs.
     *
     * @return Cost
     */
    public function negate(): Cost
    {
        $result = clone $this;

        $result->product = $this->product->negate();
        $result->supply = $this->supply->negate();
        $result->shipment = $this->shipment->negate();

        return $result;
    }

    /**
     * Returns whether this cost equals the other.
     *
     * @param Cost $other
     * @return bool
     */
    public function equals(Cost $other): bool
    {
        return $this->product->equals($other->product)
            && $this->supply->equals($other->supply)
            && $this->shipment->equals($other->shipment);
    }

    /**
     * @inheritDoc
     *
     * @param Cost $cost
     * @return int
     */
    public function compareTo($other): int
    {
        if (!$other instanceof Cost) {
            throw new UnexpectedTypeException($other, Cost::class);
        }

        if (0 !== $diff = $this->product <=> $other->product) {
            return $diff;
        }

        if (0 !== $diff = $this->supply <=> $other->supply) {
            return $diff;
        }

        if (0 !== $diff = $this->shipment <=> $other->shipment) {
            return $diff;
        }

        // TODO Compare average ?

        return 0;
    }
}
