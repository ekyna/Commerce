<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Interface OrderCreditInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderCreditInterface
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
     *
     * @return $this|OrderCreditInterface
     */
    public function setOrder(OrderInterface $order = null);

    /**
     * Sets the shipment.
     *
     * @param OrderShipmentInterface $shipment
     *
     * @return $this|OrderCreditInterface
     */
    public function setShipment(OrderShipmentInterface $shipment = null);
}
