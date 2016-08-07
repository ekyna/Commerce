<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;

/**
 * Interface OrderItemInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderItemInterface extends SaleItemInterface
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
     * Sets the parent.
     *
     * @param OrderItemInterface $parent
     * @return $this|OrderItemInterface
     */
    public function setParent(OrderItemInterface $parent = null);

    /**
     * Adds the child item.
     *
     * @param OrderItemInterface $item
     * @return $this|OrderItemInterface
     */
    public function addChild(OrderItemInterface $item);

    /**
     * Removes the child item.
     *
     * @param OrderItemInterface $item
     * @return $this|OrderItemInterface
     */
    public function removeChild(OrderItemInterface $item);

    /**
     * Adds the adjustment.
     *
     * @param OrderItemAdjustmentInterface $adjustment
     * @return $this|OrderItemInterface
     */
    public function addAdjustment(OrderItemAdjustmentInterface $adjustment);

    /**
     * Removes the adjustment.
     *
     * @param OrderItemAdjustmentInterface $adjustment
     * @return $this|OrderItemInterface
     */
    public function removeAdjustment(OrderItemAdjustmentInterface $adjustment);
}
