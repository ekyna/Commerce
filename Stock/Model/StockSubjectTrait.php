<?php

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Trait StockSubjectTrait
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait StockSubjectTrait
{
    /**
     * @var string
     */
    protected $stockMode;

    /**
     * @var string
     */
    protected $stockState;

    /**
     * @var float
     */
    protected $stockFloor;

    /**
     * @var float
     */
    protected $inStock;

    /**
     * @var float
     */
    protected $availableStock;

    /**
     * @var float
     */
    protected $virtualStock;

    /**
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;

    /**
     * @var int
     */
    protected $replenishmentTime;

    /**
     * @var string
     */
    protected $geocode;

    /**
     * @var float
     */
    protected $minimumOrderQuantity;

    /**
     * @var bool
     */
    protected $quoteOnly;

    /**
     * @var bool
     */
    protected $endOfLife;


    /**
     * Initializes the stock.
     */
    protected function initializeStock()
    {
        $this->stockMode = StockSubjectModes::MODE_AUTO;
        $this->stockState = StockSubjectStates::STATE_OUT_OF_STOCK;
        $this->stockFloor = 0;
        $this->inStock = 0;
        $this->availableStock = 0;
        $this->virtualStock = 0;
        $this->replenishmentTime = 2;
        $this->minimumOrderQuantity = 1;
        $this->quoteOnly = false;
        $this->endOfLife = false;
    }

    /**
     * Returns the stock mode.
     *
     * @return string
     */
    public function getStockMode(): string
    {
        return $this->stockMode;
    }

    /**
     * Sets the stock mode.
     *
     * @param string $mode
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockMode(string $mode): StockSubjectInterface
    {
        $this->stockMode = $mode;

        return $this;
    }

    /**
     * Returns the stock state.
     *
     * @return string
     */
    public function getStockState(): string
    {
        return $this->stockState;
    }

    /**
     * Sets the stock state.
     *
     * @param string $state
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockState(string $state): StockSubjectInterface
    {
        $this->stockState = $state;

        return $this;
    }

    /**
     * Returns the stock floor.
     *
     * @return float
     */
    public function getStockFloor(): float
    {
        return $this->stockFloor;
    }

    /**
     * Sets the stock floor.
     *
     * @param float $floor
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockFloor(float $floor): StockSubjectInterface
    {
        $this->stockFloor = $floor;

        return $this;
    }

    /**
     * Returns the in stock quantity.
     *
     * @return float
     */
    public function getInStock(): float
    {
        return $this->inStock;
    }

    /**
     * Sets the in stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setInStock(float $quantity): StockSubjectInterface
    {
        $this->inStock = $quantity;

        return $this;
    }

    /**
     * Returns the available stock quantity.
     *
     * @return float
     */
    public function getAvailableStock(): float
    {
        return $this->availableStock;
    }

    /**
     * Sets the available stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setAvailableStock(float $quantity): StockSubjectInterface
    {
        $this->availableStock = $quantity;

        return $this;
    }

    /**
     * Returns the virtual stock quantity.
     *
     * @return float
     */
    public function getVirtualStock(): float
    {
        return $this->virtualStock;
    }

    /**
     * Sets the virtual stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setVirtualStock(float $quantity): StockSubjectInterface
    {
        $this->virtualStock = (float)$quantity;

        return $this;
    }

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime
     */
    public function getEstimatedDateOfArrival(): ?\DateTime
    {
        return $this->estimatedDateOfArrival;
    }

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime $date
     *
     * @return $this|StockSubjectInterface
     */
    public function setEstimatedDateOfArrival(\DateTime $date = null): StockSubjectInterface
    {
        $this->estimatedDateOfArrival = $date;

        return $this;
    }

    /**
     * Returns the replenishment time.
     *
     * @return int
     */
    public function getReplenishmentTime(): int
    {
        return $this->replenishmentTime;
    }

    /**
     * Sets the replenishment time.
     *
     * @param int $days
     *
     * @return $this|StockSubjectInterface
     */
    public function setReplenishmentTime(int $days): StockSubjectInterface
    {
        $this->replenishmentTime = $days;

        return $this;
    }

    /**
     * Returns the geocode.
     *
     * @return string
     */
    public function getGeocode(): ?string
    {
        return $this->geocode;
    }

    /**
     * Sets the geocode.
     *
     * @param string $code
     *
     * @return $this|StockSubjectInterface
     */
    public function setGeocode(string $code = null): StockSubjectInterface
    {
        $this->geocode = $code;

        return $this;
    }

    /**
     * Returns the minimum order quantity.
     *
     * @return float
     */
    public function getMinimumOrderQuantity(): float
    {
        return $this->minimumOrderQuantity;
    }

    /**
     * Sets the minimum order quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setMinimumOrderQuantity(float $quantity): StockSubjectInterface
    {
        $this->minimumOrderQuantity = $quantity;

        return $this;
    }

    /**
     * Returns whether or not this subject is available only through quotes.
     *
     * @return bool
     */
    public function isQuoteOnly(): bool
    {
        return $this->quoteOnly;
    }

    /**
     * Sets the whether or not this subject is available only through quotes.
     *
     * @param bool $quoteOnly
     *
     * @return $this|StockSubjectInterface
     */
    public function setQuoteOnly(bool $quoteOnly): StockSubjectInterface
    {
        $this->quoteOnly = $quoteOnly;

        return $this;
    }

    /**
     * Returns the endOfLife.
     *
     * @return bool
     */
    public function isEndOfLife(): bool
    {
        return $this->endOfLife;
    }

    /**
     * Sets the endOfLife.
     *
     * @param bool $endOfLife
     *
     * @return $this|StockSubjectInterface
     */
    public function setEndOfLife(bool $endOfLife): StockSubjectInterface
    {
        $this->endOfLife = $endOfLife;

        return $this;
    }

    /**
     * Returns whether the subject is compound (ie stock is resolved regarding to children).
     *
     * @return bool
     */
    public function isStockCompound(): bool
    {
        return false;
    }

    /**
     * Returns the stock composition.
     *
     * @return StockComponent[]
     */
    public function getStockComposition(): array
    {
        /** @noinspection PhpParamsInspection */
        return [new StockComponent($this, 1)];
    }
}
