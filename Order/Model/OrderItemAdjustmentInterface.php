<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Interface OrderItemAdjustmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderItemAdjustmentInterface extends AdjustmentInterface
{
    /**
     * Returns the order item.
     *
     * @return OrderItemInterface
     */
    public function getItem();

    /**
     * Sets the order item.
     *
     * @param OrderItemInterface $item
     * @return $this|OrderAdjustmentInterface
     */
    public function setItem(OrderItemInterface $item = null);
}
