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
    protected $inStock;

    /**
     * @var float
     */
    protected $orderedStock;

    /**
     * @var float
     */
    protected $shippedStock;

    /**
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;


    /**
     * Initializes the stock.
     */
    protected function initializeStock()
    {
        $this->stockMode = StockModes::MODE_DISABLED;
        $this->stockState = StockStates::STATE_OUT_OF_STOCK;
        $this->inStock = 0;
        $this->orderedStock = 0;
        $this->shippedStock = 0;
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
     * Returns the ordered stock.
     *
     * @return float
     */
    public function getOrderedStock()
    {
        return $this->orderedStock;
    }

    /**
     * Sets the ordered stock.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setOrderedStock($quantity)
    {
        $this->orderedStock = (float)$quantity;

        return $this;
    }

    /**
     * Returns the shipped stock quantity.
     *
     * @return float
     */
    public function getShippedStock()
    {
        return $this->shippedStock;
    }

    /**
     * Sets the shipped stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setShippedStock($quantity)
    {
        $this->shippedStock = $quantity;

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
     * Returns the stock unit class.
     *
     * @return string
     */
    abstract public function getStockUnitClass();
}
