<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model as Common;

/**
 * Interface OrderInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInterface extends Common\SaleInterface
{
    /**
     * Returns the shipment state.
     *
     * @return string
     */
    public function getShipmentState();

    /**
     * Sets the shipment state.
     *
     * @param string $shipmentState
     * @return $this|OrderInterface
     */
    public function setShipmentState($shipmentState);

    /**
     * Returns whether the order has shipments or not.
     *
     * @return bool
     */
    public function hasShipments();

    /**
     * Returns the shipments.
     *
     * @return ArrayCollection|OrderShipmentInterface[]
     */
    public function getShipments();

    /**
     * Returns whether the order has the shipment or not.
     *
     * @param OrderShipmentInterface $shipment
     * @return bool
     */
    public function hasShipment(OrderShipmentInterface $shipment);

    /**
     * Adds the shipment.
     *
     * @param OrderShipmentInterface $shipment
     * @return $this|OrderInterface
     */
    public function addShipment(OrderShipmentInterface $shipment);

    /**
     * Removes the shipment.
     *
     * @param OrderShipmentInterface $shipment
     * @return $this|OrderInterface
     */
    public function removeShipment(OrderShipmentInterface $shipment);

    /**
     * Returns the "completed at" datetime.
     *
     * @return \DateTime
     */
    public function getCompletedAt();

    /**
     * Sets the "completed at" datetime.
     *
     * @param \DateTime $completedAt
     *
     * @return $this|OrderInterface
     */
    public function setCompletedAt(\DateTime $completedAt = null);
}
