<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Updater;

use DateTimeInterface;
use Decimal\Decimal;
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
     * @throws InvalidArgumentException
     */
    public function updateOrdered(Unit $unit, Decimal $quantity, bool $relative = true): void;

    /**
     * Updates the received quantity (from supplier).
     *
     * @throws InvalidArgumentException
     */
    public function updateReceived(Unit $unit, Decimal $quantity, bool $relative = true): void;

    /**
     * Updates the adjusted quantity (from administrators).
     *
     * @throws InvalidArgumentException
     */
    public function updateAdjusted(Unit $unit, Decimal $quantity, bool $relative = true): void;

    /**
     * Updates the sold quantity (from customers).
     *
     * @throws InvalidArgumentException
     */
    public function updateSold(Unit $unit, Decimal $quantity, bool $relative = true): void;

    /**
     * Updates the shipped quantity (to customers).
     *
     * @throws InvalidArgumentException
     */
    public function updateShipped(Unit $unit, Decimal $quantity, bool $relative = true): void;

    /**
     * Updates the locked quantity (for customers).
     *
     * @throws InvalidArgumentException
     */
    public function updateLocked(Unit $unit, Decimal $quantity, bool $relative = true): void;

    /**
     * Updates the estimated date of arrival.
     */
    public function updateEstimatedDateOfArrival(Unit $unit, ?DateTimeInterface $date): void;

    /**
     * Updates the net price.
     */
    public function updateNetPrice(Unit $unit, Decimal $price): void;

    /**
     * Updates the shipping price.
     */
    public function updateShippingPrice(Unit $unit, Decimal $price): void;
}
