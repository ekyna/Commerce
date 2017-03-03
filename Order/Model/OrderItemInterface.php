<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;

/**
 * Interface OrderItemInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderItemInterface extends SaleItemInterface, StockAssignmentsInterface
{
    /**
     * Returns the order.
     *
     * @return OrderInterface
     */
    public function getOrder();

    /**
     * Sets the order.
     *
     * @param OrderInterface $order
     * @return $this|OrderItemInterface
     */
    public function setOrder(OrderInterface $order = null);

    /**
     * Adds the stock unit.
     *
     * @param StockUnitInterface $unit
     *
     * @return $this|OrderItemInterface
     */
    //public function addStockUnit(StockUnitInterface $unit);

    /**
     * Removes the stock unit.
     *
     * @param StockUnitInterface $unit
     *
     * @return $this|OrderItemInterface
     */
    //public function removeStockUnit(StockUnitInterface $unit);

    /**
     * Returns the stock units.
     *
     * @return ArrayCollection|StockUnitInterface[]
     */
    //public function getStockUnits();
}
