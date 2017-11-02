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
    protected $minimumOrderQuantity;

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
     * @var int
     */
    protected $replenishmentTime;

    /**
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;


    /**
     * Initializes the stock.
     */
    protected function initializeStock()
    {
        $this->stockMode = StockSubjectModes::MODE_AUTO;
        $this->stockState = StockSubjectStates::STATE_OUT_OF_STOCK;
        $this->inStock = 0;
        $this->availableStock = 0;
        $this->virtualStock = 0;
        $this->replenishmentTime = 2;
        $this->minimumOrderQuantity = 1;
    }

    /**
     * Returns the stock mode.
     *
     * @return string
     */
    public function getStockMode()
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
    public function setStockMode($mode)
    {
        $this->stockMode = $mode;

        return $this;
    }

    /**
     * Returns the stock state.
     *
     * @return string
     */
    public function getStockState()
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
    public function setStockState($state)
    {
        $this->stockState = $state;

        return $this;
    }

    /**
     * Returns the stock floor.
     *
     * @return float
     */
    public function getStockFloor()
    {
        return $this->stockFloor;
    }

    /**
     * Sets the stock floor.
     *
     * @param float $floor
     *
     * @return StockSubjectTrait
     */
    public function setStockFloor($floor)
    {
        $this->stockFloor = $floor;

        return $this;
    }

    /**
     * Returns the minimum order quantity.
     *
     * @return float
     */
    public function getMinimumOrderQuantity()
    {
        return $this->minimumOrderQuantity;
    }

    /**
     * Sets the minimum order quantity.
     *
     * @param float $quantity
     *
     * @return StockSubjectTrait
     */
    public function setMinimumOrderQuantity($quantity)
    {
        $this->minimumOrderQuantity = $quantity;

        return $this;
    }

    /**
     * Returns the in stock quantity.
     *
     * @return float
     */
    public function getInStock()
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
    public function setInStock($quantity)
    {
        $this->inStock = (float)$quantity;

        return $this;
    }

    /**
     * Returns the available stock quantity.
     *
     * @return float
     */
    public function getAvailableStock()
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
    public function setAvailableStock($quantity)
    {
        $this->availableStock = (float)$quantity;

        return $this;
    }

    /**
     * Returns the virtual stock quantity.
     *
     * @return float
     */
    public function getVirtualStock()
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
    public function setVirtualStock($quantity)
    {
        $this->virtualStock = (float)$quantity;

        return $this;
    }

    /**
     * Returns the replenishment time.
     *
     * @return int
     */
    public function getReplenishmentTime()
    {
        return $this->replenishmentTime;
    }

    /**
     * Sets the replenishment time.
     *
     * @param int $days
     *
     * @return StockSubjectTrait
     */
    public function setReplenishmentTime($days)
    {
        $this->replenishmentTime = (int)$days;

        return $this;
    }

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime
     */
    public function getEstimatedDateOfArrival()
    {
        return $this->estimatedDateOfArrival;
    }

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime $estimatedDateOfArrival
     */
    public function setEstimatedDateOfArrival(\DateTime $estimatedDateOfArrival = null)
    {
        $this->estimatedDateOfArrival = $estimatedDateOfArrival;
    }

    /**
     * Returns whether the subject is compound (ie stock is resolved regarding to children).
     *
     * @return bool
     */
    public function isStockCompound()
    {
        return false;
    }
}
