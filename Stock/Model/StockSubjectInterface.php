<?php

namespace Ekyna\Component\Commerce\Stock\Model;

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
     * Returns the stock floor.
     *
     * @return float
     */
    public function getStockFloor();

    /**
     * Sets the stock floor.
     *
     * @param float $floor
     *
     * @return $this|StockSubjectInterface
     */
    public function setStockFloor($floor);

    /**
     * Returns the in stock quantity.
     *
     * @return float
     */
    public function getInStock();

    /**
     * Sets the in stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setInStock($quantity);

    /**
     * Returns the available stock quantity.
     *
     * @return float
     */
    public function getAvailableStock();

    /**
     * Sets the available stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setAvailableStock($quantity);

    /**
     * Returns the virtual stock quantity.
     *
     * @return float
     */
    public function getVirtualStock();

    /**
     * Sets the virtual stock quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setVirtualStock($quantity);

    /**
     * Returns the estimated date of arrival.
     *
     * @return \DateTime
     */
    public function getEstimatedDateOfArrival();

    /**
     * Sets the estimated date of arrival.
     *
     * @param \DateTime $eda
     *
     * @return $this|StockSubjectInterface
     */
    public function setEstimatedDateOfArrival(\DateTime $eda = null);

    /**
     * Returns the replenishment time.
     *
     * @return int
     */
    public function getReplenishmentTime();

    /**
     * Sets the replenishment time.
     *
     * @param int $days
     *
     * @return $this|StockSubjectInterface
     */
    public function setReplenishmentTime($days);

    /**
     * Returns the geocode.
     *
     * @return string
     */
    public function getGeocode();

    /**
     * Sets the geocode.
     *
     * @param string $code
     *
     * @return $this|StockSubjectInterface
     */
    public function setGeocode($code);

    /**
     * Returns the minimum order quantity.
     *
     * @return float
     */
    public function getMinimumOrderQuantity();

    /**
     * Sets the minimum order quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockSubjectInterface
     */
    public function setMinimumOrderQuantity($quantity);

    /**
     * Returns whether or not this subject is available only through quotes.
     *
     * @return bool
     */
    public function isQuoteOnly();

    /**
     * Sets the whether or not this subject is available only through quotes.
     *
     * @param bool $quoteOnly
     *
     * @return $this|StockSubjectInterface
     */
    public function setQuoteOnly($quoteOnly);

    /**
     * Returns the endOfLife.
     *
     * @return bool
     */
    public function isEndOfLife();

    /**
     * Sets the endOfLife.
     *
     * @param bool $endOfLife
     *
     * @return $this|StockSubjectInterface
     */
    public function setEndOfLife($endOfLife);

    /**
     * Returns whether the subject is compound (ie stock is resolved regarding to children).
     *
     * @return bool
     */
    public function isStockCompound();

    /**
     * Returns the stock unit class.
     *
     * @return string
     */
    public static function getStockUnitClass();
}
