<?php

namespace Ekyna\Component\Commerce\Stock\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface StockAssignmentInterface
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface StockAssignmentInterface extends ResourceInterface
{
    /**
     * Returns the stock unit.
     *
     * @return StockUnitInterface
     */
    public function getStockUnit();

    /**
     * Sets the stock unit.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return $this|StockAssignmentInterface
     */
    public function setStockUnit(StockUnitInterface $stockUnit = null);

    /**
     * Returns the sale item.
     *
     * @return SaleItemInterface|StockAssignmentsInterface
     */
    public function getSaleItem();

    /**
     * Sets the sale item.
     *
     * @param SaleItemInterface $saleItem
     *
     * @return $this|StockAssignmentInterface
     */
    public function setSaleItem(SaleItemInterface $saleItem = null);

    /**
     * Returns the sold quantity.
     *
     * @return float
     */
    public function getSoldQuantity(): float;

    /**
     * Sets the sold quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockAssignmentInterface
     */
    public function setSoldQuantity(float $quantity): StockAssignmentInterface;

    /**
     * Returns the shipped quantity.
     *
     * @return float
     */
    public function getShippedQuantity(): float;

    /**
     * Sets the shipped quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockAssignmentInterface
     */
    public function setShippedQuantity(float $quantity): StockAssignmentInterface;

    /**
     * Returns the shippable quantity.
     *
     * @return float
     */
    public function getShippableQuantity(): float;

    /**
     * Returns whether or not the assignment is fully shipped.
     *
     * @return bool
     */
    public function isFullyShipped(): bool;

    /**
     * Returns whether or not the assignment is fully shippable.
     *
     * @return bool
     */
    public function isFullyShippable(): bool;

    /**
     * REturns whether the assignment is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
