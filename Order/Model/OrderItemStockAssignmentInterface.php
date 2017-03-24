<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;

/**
 * Interface OrderItemStockAssignmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderItemStockAssignmentInterface extends StockAssignmentInterface
{
    /**
     * Returns the order item.
     *
     * @return OrderItemInterface
     */
    public function getOrderItem();

    /**
     * Sets the order item.
     *
     * @param OrderItemInterface $orderItem
     *
     * @return OrderItemStockAssignmentInterface
     */
    public function setOrderItem(OrderItemInterface $orderItem = null);

    /**
     * Returns the shipped quantity.
     *
     * @return float
     */
    public function getShippedQuantity();

    /**
     * Sets the shipped quantity.
     *
     * @param float $shippedQuantity
     *
     * @return OrderItemStockAssignmentInterface
     */
    public function setShippedQuantity($shippedQuantity);
}
