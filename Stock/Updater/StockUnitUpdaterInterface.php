<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

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
     * @param StockUnitInterface $unit
     * @param float              $quantity
     * @param bool               $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateOrdered(StockUnitInterface $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the received quantity (from supplier).
     *
     * @param StockUnitInterface $unit
     * @param float              $quantity
     * @param bool               $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateReceived(StockUnitInterface $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the adjusted quantity (from administrators).
     *
     * @param StockUnitInterface $unit
     * @param float              $quantity
     * @param bool               $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateAdjusted(StockUnitInterface $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the sold quantity (from customers).
     *
     * @param StockUnitInterface $unit
     * @param float              $quantity
     * @param bool               $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateSold(StockUnitInterface $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the shipped quantity (to customers).
     *
     * @param StockUnitInterface $unit
     * @param float              $quantity
     * @param bool               $relative
     *
     * @throws InvalidArgumentException
     */
    public function updateShipped(StockUnitInterface $unit, float $quantity, bool $relative = true): void;

    /**
     * Updates the estimated date of arrival.
     *
     * @param StockUnitInterface $unit
     * @param \DateTime          $date
     */
    public function updateEstimatedDateOfArrival(StockUnitInterface $unit, \DateTime $date): void;

    /**
     * Updates the net price.
     *
     * @param StockUnitInterface $unit
     * @param float              $price
     */
    public function updateNetPrice(StockUnitInterface $unit, float $price): void;

    /**
     * Updates the shipping price.
     *
     * @param StockUnitInterface $unit
     * @param float              $price
     */
    public function updateShippingPrice(StockUnitInterface $unit, float $price): void;
}
