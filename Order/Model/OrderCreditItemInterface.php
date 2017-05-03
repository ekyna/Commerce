<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Interface OrderCreditItemInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderCreditItemInterface
{
    /**
     * Set the order item.
     *
     * @param OrderItemInterface $orderItem
     *
     * @return $this|OrderCreditItemInterface
     */
    public function setOrderItem(OrderItemInterface $orderItem);

    /**
     * Returns the order item.
     *
     * @return OrderItemInterface
     */
    public function getOrderItem();
}
