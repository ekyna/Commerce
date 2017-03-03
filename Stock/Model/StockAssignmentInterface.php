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
    public function setStockUnit(StockUnitInterface $stockUnit);

    /**
     * Returns the sale item.
     *
     * @return SaleItemInterface
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
     * Returns the quantity.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Sets the quantity.
     *
     * @param float $quantity
     *
     * @return $this|StockAssignmentInterface
     */
    public function setQuantity($quantity);
}
