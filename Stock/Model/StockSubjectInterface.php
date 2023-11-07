<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Subject\Model\SubjectInterface;

/**
 * Interface StockSubjectInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockSubjectInterface extends SubjectInterface
{
    public function getStockMode(): string;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setStockMode(string $mode): StockSubjectInterface;

    public function getStockState(): string;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setStockState(string $state): StockSubjectInterface;

    public function getStockFloor(): Decimal;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setStockFloor(Decimal $floor): StockSubjectInterface;

    public function getInStock(): Decimal;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setInStock(Decimal $quantity): StockSubjectInterface;

    public function getAvailableStock(): Decimal;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setAvailableStock(Decimal $quantity): StockSubjectInterface;

    public function getVirtualStock(): Decimal;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setVirtualStock(Decimal $quantity): StockSubjectInterface;

    public function getEstimatedDateOfArrival(): ?DateTimeInterface;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setEstimatedDateOfArrival(?DateTimeInterface $eda): StockSubjectInterface;

    public function getReplenishmentTime(): int;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setReplenishmentTime(int $days): StockSubjectInterface;

    public function getGeocode(): ?string;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setGeocode(?string $code): StockSubjectInterface;

    public function getMinimumOrderQuantity(): Decimal;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setMinimumOrderQuantity(Decimal $quantity): StockSubjectInterface;

    public function getReleasedAt(): ?DateTimeInterface;

    public function setReleasedAt(?DateTimeInterface $date): StockSubjectInterface;

    /**
     * Returns whether this subject is available only through quotes.
     *
     * @return bool
     */
    public function isQuoteOnly(): bool;

    /**
     * Sets the whether his subject is available only through quotes.
     *
     * @param bool $quoteOnly
     *
     * @return $this|StockSubjectInterface
     */
    public function setQuoteOnly(bool $quoteOnly): StockSubjectInterface;

    public function isEndOfLife(): bool;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setEndOfLife(bool $endOfLife): StockSubjectInterface;

    public function isPhysical(): bool;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setPhysical(bool $physical): StockSubjectInterface;

    public function getUnit(): string;

    /**
     * @return $this|StockSubjectInterface
     */
    public function setUnit(string $unit): StockSubjectInterface;

    /**
     * Returns the subject weight (kilograms).
     */
    public function getWeight(): Decimal;

    /**
     * Sets the subject weight (kilograms).
     *
     * @return $this|StockSubjectInterface
     */
    public function setWeight(Decimal $weight): StockSubjectInterface;

    /**
     * Returns the subject width (millimeters).
     */
    public function getWidth(): int;

    /**
     * Sets the subject width (millimeters).
     *
     * @return $this|StockSubjectInterface
     */
    public function setWidth(int $width): StockSubjectInterface;

    /**
     * Returns the subject height (millimeters).
     */
    public function getHeight(): int;

    /**
     * Sets the subject height (millimeters).
     *
     * @return $this|StockSubjectInterface
     */
    public function setHeight(int $height): StockSubjectInterface;

    /**
     * Returns the subject depth (millimeters).
     */
    public function getDepth(): int;

    /**
     * Sets the subject depth (millimeters).
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
     */
    public function getPackageWeight(): Decimal;

    /**
     * Sets the package weight (kilograms).
     *
     * @return $this|StockSubjectInterface
     */
    public function setPackageWeight(Decimal $weight): StockSubjectInterface;

    /**
     * Returns the package height (millimeters).
     */
    public function getPackageHeight(): int;

    /**
     * Sets the package height (millimeters).
     *
     * @return $this|StockSubjectInterface
     */
    public function setPackageHeight(int $height): StockSubjectInterface;

    /**
     * Returns the package width (millimeters).
     */
    public function getPackageWidth(): int;

    /**
     * Sets the package width (millimeters).
     *
     * @return $this|StockSubjectInterface
     */
    public function setPackageWidth(int $width): StockSubjectInterface;

    /**
     * Returns the package depth (millimeters).
     */
    public function getPackageDepth(): int;

    /**
     * Sets the package depth (millimeters).
     *
     * @return $this|StockSubjectInterface
     */
    public function setPackageDepth(int $depth): StockSubjectInterface;

    /**
     * Returns the « Harmonized System » code.
     *
     * @return string|null
     */
    public function getHsCode(): ?string;

    /**
     * Sets the « Harmonized System » code.
     *
     * @return $this|StockSubjectInterface
     */
    public function setHsCode(?string $hsCode): StockSubjectInterface;
    /**
     * Returns whether all the package dimensions are set.
     */
    public function hasPackageDimensions(): bool;

    /**
     * Returns whether the subject is compound (ie stock is resolved regarding children).
     */
    public function isStockCompound(): bool;

    /**
     * @return array<StockComponent>
     */
    public function getStockComposition(): array;

    public static function getStockUnitClass(): string;
}
