<?php


namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Interface ShipmentItemInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentItemInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the shipment.
     *
     * @return ShipmentInterface
     */
    public function getShipment();

    /**
     * Sets the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return $this|ShipmentItemInterface
     */
    public function setShipment(ShipmentInterface $shipment = null);

    /**
     * Returns the orderItem.
     *
     * @return OrderItemInterface
     */
    public function getOrderItem();

    /**
     * Sets the orderItem.
     *
     * @param OrderItemInterface $orderItem
     *
     * @return $this|ShipmentItemInterface
     */
    public function setOrderItem(OrderItemInterface $orderItem = null);

    /**
     * Returns the quantity.
     *
     * @return int
     */
    public function getQuantity();

    /**
     * Sets the quantity.
     *
     * @param int $quantity
     *
     * @return $this|ShipmentItemInterface
     */
    public function setQuantity($quantity);
}
