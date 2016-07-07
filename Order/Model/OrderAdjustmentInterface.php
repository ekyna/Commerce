<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Interface OrderAdjustmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderAdjustmentInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

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
