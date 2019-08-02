<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Interface StockSubjectInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockSubjectInterface extends SubjectInterface
{
    /**
     * Returns the stock mode.
     *
     * @return string
     */
    public function getStockMode(): string;

    /**
     * Sets the stock mode.
     *
     * @param string $mode
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockMode(string $mode): StockSubjectInterface;

    /**
     * Returns the stock state.
     *
     * @return string
     */
    public function getStockState(): string;

    /**
     * Sets the stock state.
     *
     * @param string $state
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockState(string $state): StockSubjectInterface;

    /**
     * Returns the stock floor.
     *
     * @return float
     */
    public function getStockFloor(): float;

    /**
     * Sets the stock floor.
     *
     * @param float $floor
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockFloor(float $floor): StockSubjectInterface;

    /**
     * Returns the in stock quantity.
     *
     * @return float
     */
    public function getInStock(): float;

    /**
     * Sets the in stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setInStock(float $quantity): StockSubjectInterface;

    /**
     * Returns the available stock quantity.
     *
     * @return float
     */
    public function getAvailableStock(): float;

    /**
     * Sets the available stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setAvailableStock(float $quantity): StockSubjectInterface;

    /**
     * Returns the virtual stock quantity.
     *
     * @return float
     */
    public function getVirtualStock(): float;

    /**
     * Sets the virtual stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setVirtualStock(float $quantity): StockSubjectInterface;

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime
     */
    public function getEstimatedDateOfArrival(): ?\DateTime;

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime $eda
     *
     * @return $this|StockSubjectInterface
     */
    public function setEstimatedDateOfArrival(\DateTime $eda = null): StockSubjectInterface;

    /**
     * Returns the replenishment time.
     *
     * @return int
     */
    public function getReplenishmentTime(): int;

    /**
     * Sets the replenishment time.
     *
     * @param int $days
     *
     * @return $this|StockSubjectInterface
     */
    public function setReplenishmentTime(int $days);

    /**
     * Returns the geocode.
     *
     * @return string
     */
    public function getGeocode(): ?string;

    /**
     * Sets the geocode.
     *
     * @param string $code
     *
     * @return $this|StockSubjectInterface
     */
    public function setGeocode(string $code = null): StockSubjectInterface;

    /**
     * Returns the minimum order quantity.
     *
     * @return float
     */
    public function getMinimumOrderQuantity(): float;

    /**
     * Sets the minimum order quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setMinimumOrderQuantity(float $quantity): StockSubjectInterface;

    /**
     * Returns whether or not this subject is available only through quotes.
     *
     * @return bool
     */
    public function isQuoteOnly(): bool;

    /**
     * Sets the whether or not this subject is available only through quotes.
     *
     * @param bool $quoteOnly
     *
     * @return $this|StockSubjectInterface
     */
    public function setQuoteOnly(bool $quoteOnly): StockSubjectInterface;

    /**
     * Returns the endOfLife.
     *
     * @return bool
     */
    public function isEndOfLife(): bool;

    /**
     * Sets the endOfLife.
     *
     * @param bool $endOfLife
     *
     * @return $this|StockSubjectInterface
     */
    public function setEndOfLife(bool $endOfLife): StockSubjectInterface;

    /**
     * Returns whether the subject is compound (ie stock is resolved regarding to children).
     *
     * @return bool
     */
    public function isStockCompound(): bool;

    /**
     * Returns the stock composition.
     *
     * @return StockComponent[]
     */
    public function getStockComposition(): array;

    /**
     * Returns the stock unit class.
     *
     * @return string
     */
    public static function getStockUnitClass(): string;
}
