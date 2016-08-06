<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Interface OrderAdjustmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderAdjustmentInterface extends AdjustmentInterface
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
     * @return $this|OrderAdjustmentInterface
     */
    public function setOrder(OrderInterface $order = null);
}
