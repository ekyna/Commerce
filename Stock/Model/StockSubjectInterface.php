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
     * Returns the quantity unit.
     *
     * @return string
     */
    public function getUnit(): string;

    /**
     * Sets the quantity unit.
     *
     * @param string $unit
     *
     * @return $this|StockSubjectInterface
     */
    public function setUnit(string $unit): StockSubjectInterface;

    /**
     * Returns the subject weight (kilograms).
     *
     * @return float
     */
    public function getWeight(): ?float;

    /**
     * Sets the subject weight.
     *
     * @param float $weight
     *
     * @return $this|StockSubjectInterface
     */
    public function setWeight(float $weight): StockSubjectInterface;

    /**
     * Returns the subject width (millimeters).
     *
     * @return int
     */
    public function getWidth(): int;

    /**
     * Sets the subject width.
     *
     * @param int $width
     *
     * @return $this|StockSubjectInterface
     */
    public function setWidth(int $width): StockSubjectInterface;

    /**
     * Returns the subject height (millimeters).
     *
     * @return int
     */
    public function getHeight(): int;

    /**
     * Sets the subject height.
     *
     * @param int $height
     *
     * @return $this|StockSubjectInterface
     */
    public function setHeight(int $height): StockSubjectInterface;

    /**
     * Returns the subject depth (millimeters).
     *
     * @return int
     */
    public function getDepth(): int;

    /**
     * Sets the subject depth.
     *
     * @param int $depth
     *
     * @return $this|StockSubjectInterface
     */
    public function setDepth(int $depth): StockSubjectInterface;

    /**
     * Returns whether all the subject dimensions are set.
     *
     * @return bool
     */
    public function hasDimensions(): bool;

    /**
     * Returns the package weight (kilograms).
     *
     * @return float
     */
    public function getPackageWeight(): float;

    /**
     * Sets the package weight (kilograms).
     *
     * @param float $weight
     *
     * @return $this|StockSubjectInterface
     */
    public function setPackageWeight(float $weight): StockSubjectInterface;

    /**
     * Returns the package height (millimeters).
     *
     * @return int
     */
    public function getPackageHeight(): ?int;

    /**
     * Sets the package height (millimeters).
     *
     * @param int $height
     *
     * @return $this|StockSubjectInterface
     */
    public function setPackageHeight(int $height): StockSubjectInterface;

    /**
     * Returns the package width (millimeters).
     *
     * @return int
     */
    public function getPackageWidth(): int;

    /**
     * Sets the package width (millimeters).
     *
     * @param int $width
     *
     * @return $this|StockSubjectInterface
     */
    public function setPackageWidth(int $width): StockSubjectInterface;

    /**
     * Returns the package depth (millimeters).
     *
     * @return int
     */
    public function getPackageDepth(): int;

    /**
     * Sets the package depth (millimeters).
     *
     * @param int $depth
     *
     * @return $this|StockSubjectInterface
     */
    public function setPackageDepth(int $depth): StockSubjectInterface;

    /**
     * Returns whether all the package dimensions are set.
     *
     * @return bool
     */
    public function hasPackageDimensions(): bool;

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
