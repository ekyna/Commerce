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
    protected $virtualStock;

    /**
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;


    /**
     * Initializes the stock.
     */
    protected function initializeStock()
    {
        $this->stockMode = StockSubjectModes::MODE_DISABLED;
        $this->stockState = StockSubjectStates::STATE_OUT_OF_STOCK;
        $this->inStock = 0;
        $this->virtualStock = 0;
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
     * Returns the virtualStock.
     *
     * @return float
     */
    public function getVirtualStock()
    {
        return $this->virtualStock;
    }

    /**
     * Sets the virtualStock.
     *
     * @param float $virtualStock
     *
     * @return $this|StockSubjectInterface
     */
    public function setVirtualStock($virtualStock)
    {
        $this->virtualStock = (float)$virtualStock;

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
}
