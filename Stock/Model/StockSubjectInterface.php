<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StockSubjectInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockSubjectInterface extends ResourceInterface
{
    /**
     * Returns the stock mode.
     *
     * @return string
     */
    public function getStockMode();

    /**
     * Sets the stock mode.
     *
     * @param string $mode
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockMode($mode);

    /**
     * Returns the stock state.
     *
     * @return string
     */
    public function getStockState();

    /**
     * Sets the stock state.
     *
     * @param string $state
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockState($state);

    /**
     * Returns the stock.
     *
     * @return float
     */
    public function getStock();

    /**
     * Sets the stock.
     *
     * @param float $stock
     *
     * @return $this|StockSubjectInterface
     */
    public function setStock($stock);

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime
     */
    public function getEstimatedDateOfArrival();

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime $eta
     *
     * @return $this|StockSubjectInterface
     */
    public function setEstimatedDateOfArrival(\DateTime $eta);

    /**
     * Returns whether or not the subject has at least one stock unit.
     *
     * @return bool
     */
    //public function hasStockUnits();

    /**
     * Returns the stock units.
     *
     * @return ArrayCollection|StockUnitInterface[]
     */
    //public function getStockUnits();

    /**
     * Returns whether or not the subject has the given stock unit.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return bool
     */
    //public function hasStockUnit(StockUnitInterface $stockUnit);

    /**
     * Returns whether or not the subject has the given stock unit.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return $this|StockSubjectInterface
     */
    //public function addStockUnit(StockUnitInterface $stockUnit);

    /**
     * Returns whether or not the subject has the given stock unit.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return $this|StockSubjectInterface
     */
    //public function removeStockUnit(StockUnitInterface $stockUnit);

    /**
     * Returns the stock unit class.
     *
     * @return string
     */
    public function getStockUnitClass();
}
