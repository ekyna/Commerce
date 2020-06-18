<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface as Unit;

/**
 * Interface StockUnitUpdaterInterface
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockUnitUpdaterInterface
{
    /**
     * Updates the ordered quantity (to supplier).
     *
     * @param Unit  $unit
     * @param float $quantity
     * @param bool  $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateOrdered(Unit $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the received quantity (from supplier).
     *
     * @param Unit  $unit
     * @param float $quantity
     * @param bool  $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateReceived(Unit $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the adjusted quantity (from administrators).
     *
     * @param Unit  $unit
     * @param float $quantity
     * @param bool  $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateAdjusted(Unit $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the sold quantity (from customers).
     *
     * @param Unit  $unit
     * @param float $quantity
     * @param bool  $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateSold(Unit $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the shipped quantity (to customers).
     *
     * @param Unit  $unit
     * @param float $quantity
     * @param bool  $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateShipped(Unit $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the locked quantity (for customers).
     *
     * @param Unit  $unit
     * @param float $quantity
     * @param bool  $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateLocked(Unit $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the estimated date of arrival.
     *
     * @param Unit      $unit
     * @param \DateTime $date
     */
    public function updateEstimatedDateOfArrival(Unit $unit, \DateTime $date): void;

    /**
     * Updates the net price.
     *
     * @param Unit  $unit
     * @param float $price
     */
    public function updateNetPrice(Unit $unit, float $price): void;

    /**
     * Updates the shipping price.
     *
     * @param Unit  $unit
     * @param float $price
     */
    public function updateShippingPrice(Unit $unit, float $price): void;
}
