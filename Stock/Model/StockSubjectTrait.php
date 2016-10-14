<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Trait StockSubjectTrait
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait StockSubjectTrait
{
    /**
     * @var ArrayCollection|StockUnitInterface[]
     */
    //protected $stockUnits;

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
    protected $stock;

    /**
     * @var \DateTime
     */
    protected $estimatedDateOfArrival;


    /**
     * Initializes the stock.
     */
    protected function initializeStock()
    {
        //$this->stockUnits = new ArrayCollection();
        $this->stockMode = StockModes::MODE_DISABLED;
        $this->stockState = StockStates::STATE_OUT_OF_STOCK;
        $this->stock = 0;
    }

    /**
     * @inheritdoc
     */
    public function getStockMode()
    {
        return $this->stockMode;
    }

    /**
     * @inheritdoc
     */
    public function setStockMode($mode)
    {
        $this->stockMode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStockState()
    {
        return $this->stockState;
    }

    /**
     * @inheritdoc
     */
    public function setStockState($state)
    {
        $this->stockState = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @inheritdoc
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

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
     * @inheritdoc
     */
    /*public function hasStockUnits()
    {
        return 0 < $this->stockUnits->count();
    }*/

    /**
     * @inheritdoc
     */
    /*public function getStockUnits()
    {
        return $this->stockUnits;
    }*/

    /**
     * @inheritdoc
     */
    /*public function hasStockUnit(StockUnitInterface $stockUnit)
    {
        return $this->stockUnits->contains($stockUnit);
    }*/

    /**
     * @inheritdoc
     */
    /*public function addStockUnit(StockUnitInterface $stockUnit)
    {
        $this->validateStockUnitClass($stockUnit);

        if (!$this->hasStockUnit($stockUnit)) {
            $stockUnit->setSubject($this);
            $this->stockUnits->add($stockUnit);
        }

        return $this;
    }*/

    /**
     * @inheritdoc
     */
    /*public function removeStockUnit(StockUnitInterface $stockUnit)
    {
        $this->validateStockUnitClass($stockUnit);

        if ($this->hasStockUnit($stockUnit)) {
            $stockUnit->setSubject(null);
            $this->stockUnits->removeElement($stockUnit);
        }

        return $this;
    }*/

    //abstract protected function validateStockUnitClass(StockUnitInterface $stockUnit);

    abstract public function getStockUnitClass();
}
