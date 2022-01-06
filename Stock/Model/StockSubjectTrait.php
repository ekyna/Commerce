<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Model;

use DateTimeInterface;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Units;
use Ekyna\Component\Commerce\Subject\Model\SubjectTrait;

/**
 * Trait StockSubjectTrait
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait StockSubjectTrait
{
    use SubjectTrait {
        __clone as subjectClone;
    }

    protected string             $stockMode;
    protected string             $stockState;
    protected Decimal            $stockFloor;
    protected Decimal            $inStock;
    protected Decimal            $availableStock;
    protected Decimal            $virtualStock;
    protected ?DateTimeInterface $estimatedDateOfArrival = null;
    protected int                $replenishmentTime;
    protected ?string            $geocode                = null;
    protected Decimal            $minimumOrderQuantity;
    protected bool               $quoteOnly;
    protected bool               $endOfLife;
    protected Decimal            $weight;
    protected int                $height;
    protected int                $width;
    protected int                $depth;
    protected string             $unit;
    protected Decimal            $packageWeight;
    protected int                $packageHeight;
    protected int                $packageWidth;
    protected int                $packageDepth;

    protected function initializeStock()
    {
        $this->initializeSubject();

        $this->stockMode = StockSubjectModes::MODE_AUTO;
        $this->stockState = StockSubjectStates::STATE_OUT_OF_STOCK;
        $this->stockFloor = new Decimal(0);
        $this->inStock = new Decimal(0);
        $this->availableStock = new Decimal(0);
        $this->virtualStock = new Decimal(0);
        $this->replenishmentTime = 2;
        $this->minimumOrderQuantity = new Decimal(1);
        $this->quoteOnly = false;
        $this->endOfLife = false;
        $this->unit = Units::PIECE;
        $this->weight = new Decimal(0);
        $this->width = 0;
        $this->height = 0;
        $this->depth = 0;
        $this->packageWeight = new Decimal(0);
        $this->packageWidth = 0;
        $this->packageHeight = 0;
        $this->packageDepth = 0;
    }

    public function __clone()
    {
        $this->subjectClone();

        $this->stockFloor = clone $this->stockFloor;
        $this->inStock = clone $this->inStock;
        $this->availableStock = clone $this->availableStock;
        $this->virtualStock = clone $this->virtualStock;
        $this->minimumOrderQuantity = clone $this->minimumOrderQuantity;
        $this->weight = clone $this->weight;
        $this->packageWeight = clone $this->packageWeight;
    }

    public function getStockMode(): string
    {
        return $this->stockMode;
    }

    public function setStockMode(string $mode): StockSubjectInterface
    {
        $this->stockMode = $mode;

        return $this;
    }

    public function getStockState(): string
    {
        return $this->stockState;
    }

    public function setStockState(string $state): StockSubjectInterface
    {
        $this->stockState = $state;

        return $this;
    }

    public function getStockFloor(): Decimal
    {
        return $this->stockFloor;
    }

    public function setStockFloor(Decimal $floor): StockSubjectInterface
    {
        $this->stockFloor = $floor;

        return $this;
    }

    public function getInStock(): Decimal
    {
        return $this->inStock;
    }

    public function setInStock(Decimal $quantity): StockSubjectInterface
    {
        $this->inStock = $quantity;

        return $this;
    }

    public function getAvailableStock(): Decimal
    {
        return $this->availableStock;
    }

    public function setAvailableStock(Decimal $quantity): StockSubjectInterface
    {
        $this->availableStock = $quantity;

        return $this;
    }

    public function getVirtualStock(): Decimal
    {
        return $this->virtualStock;
    }

    public function setVirtualStock(Decimal $quantity): StockSubjectInterface
    {
        $this->virtualStock = $quantity;

        return $this;
    }

    public function getEstimatedDateOfArrival(): ?DateTimeInterface
    {
        return $this->estimatedDateOfArrival;
    }

    public function setEstimatedDateOfArrival(?DateTimeInterface $date): StockSubjectInterface
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }

    public function getReplenishmentTime(): int
    {
        return $this->replenishmentTime;
    }

    public function setReplenishmentTime(int $days): StockSubjectInterface
    {
        $this->replenishmentTime = $days;

        return $this;
    }

    public function getGeocode(): ?string
    {
        return $this->geocode;
    }

    public function setGeocode(?string $code): StockSubjectInterface
    {
        $this->geocode = $code;

        return $this;
    }

    public function getMinimumOrderQuantity(): Decimal
    {
        return $this->minimumOrderQuantity;
    }

    public function setMinimumOrderQuantity(Decimal $quantity): StockSubjectInterface
    {
        $this->minimumOrderQuantity = $quantity;

        return $this;
    }

    public function isQuoteOnly(): bool
    {
        return $this->quoteOnly;
    }

    public function setQuoteOnly(bool $quoteOnly): StockSubjectInterface
    {
        $this->quoteOnly = $quoteOnly;

        return $this;
    }

    public function isEndOfLife(): bool
    {
        return $this->endOfLife;
    }

    public function setEndOfLife(bool $endOfLife): StockSubjectInterface
    {
        $this->endOfLife = $endOfLife;

        return $this;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): StockSubjectInterface
    {
        $this->unit = $unit;

        return $this;
    }

    public function getWeight(): Decimal
    {
        return $this->weight;
    }

    public function setWeight(Decimal $weight): StockSubjectInterface
    {
        $this->weight = $weight;

        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): StockSubjectInterface
    {
        $this->width = $width;

        return $this;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function setHeight(int $height): StockSubjectInterface
    {
        $this->height = $height;

        return $this;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function setDepth(int $depth): StockSubjectInterface
    {
        $this->depth = $depth;

        return $this;
    }

    public function hasDimensions(): bool
    {
        return !empty($this->width) && !empty($this->height) && !empty($this->depth);
    }

    public function getPackageWeight(): Decimal
    {
        return $this->packageWeight;
    }

    public function setPackageWeight(Decimal $weight): StockSubjectInterface
    {
        $this->packageWeight = $weight;

        return $this;
    }

    public function getPackageHeight(): int
    {
        return $this->packageHeight;
    }

    public function setPackageHeight(int $height): StockSubjectInterface
    {
        $this->packageHeight = $height;

        return $this;
    }

    public function getPackageWidth(): int
    {
        return $this->packageWidth;
    }

    public function setPackageWidth(int $width): StockSubjectInterface
    {
        $this->packageWidth = $width;

        return $this;
    }

    public function getPackageDepth(): int
    {
        return $this->packageDepth;
    }

    public function setPackageDepth(int $depth): StockSubjectInterface
    {
        $this->packageDepth = $depth;

        return $this;
    }

    public function hasPackageDimensions(): bool
    {
        return !empty($this->packageWidth) && !empty($this->packageHeight) && !empty($this->packageDepth);
    }

    public function isStockCompound(): bool
    {
        return false;
    }

    public function getStockComposition(): array
    {
        return [new StockComponent($this, new Decimal(1))];
    }
}
