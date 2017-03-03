<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;

/**
 * Interface OrderShipmentItemInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderShipmentItemInterface extends ShipmentItemInterface
{
    /**
     * Set the order item.
     *
     * @param OrderItemInterface $orderItem
     *
     * @return $this|OrderShipmentItemInterface
     */
    public function setOrderItem(OrderItemInterface $orderItem);

    /**
     * Returns the order item.
     *
     * @return OrderItemInterface
     */
    public function getOrderItem();
}
