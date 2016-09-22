<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface OrderShipmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderShipmentInterface extends ShipmentInterface
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
     * @return $this|OrderShipmentInterface
     */
    public function setOrder(OrderInterface $order = null);
}
