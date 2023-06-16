<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use Decimal\Decimal;
use Doctrine\Common\Comparable;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;

/**
 * Class Revenue
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class Revenue implements Comparable
{
    public function __construct(
        private Decimal $product = new Decimal(0),
        private Decimal $shipment = new Decimal(0),
    ) {
    }

    public function __clone(): void
    {
        $this->product = clone $this->product;
        $this->shipment = clone $this->shipment;
    }

    /**
     * Returns the product revenue.
     *
     * @return Decimal
     */
    public function getProduct(): Decimal
    {
        return $this->product;
    }

    /**
     * Adds the given amount to product revenue.
     *
     * @param Decimal $amount
     * @return void
     */
    public function addProduct(Decimal $amount): void
    {
        $this->product += $amount;
    }

    /**
     * Returns the shipment revenue.
     *
     * @return Decimal
     */
    public function getShipment(): Decimal
    {
        return $this->shipment;
    }

    /**
     * Adds the given amount to shipment revenue.
     *
     * @param Decimal $amount
     * @return void
     */
    public function addShipment(Decimal $amount): void
    {
        $this->shipment += $amount;
    }

    /**
     * Returns this revenue total.
     *
     * @param bool $gross Whether to include shipment revenue.
     * @return Decimal
     */
    public function getTotal(bool $gross): Decimal
    {
        if ($gross) {
            return $this->product;
        }

        return $this->product->add($this->shipment);
    }

    /**
     * Adds the given revenue to this one.
     *
     * @param Revenue $revenue
     * @return Revenue
     */
    public function add(Revenue $revenue): Revenue
    {
        $this->product += $revenue->product;
        $this->shipment += $revenue->shipment;

        return $this;
    }

    /**
     * Multiplies this revenue.
     *
     * @param Decimal $quantity
     * @return Revenue
     */
    public function multiply(Decimal $quantity): Revenue
    {
        $this->product *= $quantity;
        $this->shipment *= $quantity;

        return $this;
    }

    /**
     * Negates the revenue.
     *
     * @return Revenue
     */
    public function negate(): Revenue
    {
        $this->product = $this->product->negate();
        $this->shipment = $this->shipment->negate();

        return $this;
    }

    /**
     * Returns whether this revenue equals the other.
     *
     * @param Revenue $other
     * @return bool
     */
    public function equals(Revenue $other): bool
    {
        return $this->product->equals($other->product)
            && $this->shipment->equals($other->shipment);
    }

    /**
     * @inheritDoc
     *
     * @param Revenue $cost
     * @return int
     */
    public function compareTo($other): int
    {
        if (!$other instanceof Revenue) {
            throw new UnexpectedTypeException($other, Revenue::class);
        }

        if (0 !== $diff = $this->product <=> $other->product) {
            return $diff;
        }

        if (0 !== $diff = $this->shipment <=> $other->shipment) {
            return $diff;
        }

        return 0;
    }
}
