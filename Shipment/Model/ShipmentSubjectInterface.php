<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class ShipmentSubjectInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentSubjectInterface
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
     * @return $this|ShipmentSubjectInterface
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
     * @return ArrayCollection|ShipmentInterface[]
     */
    public function getShipments();

    /**
     * Returns whether the order has the shipment or not.
     *
     * @param ShipmentInterface $shipment
     * @return bool
     */
    public function hasShipment(ShipmentInterface $shipment);

    /**
     * Adds the shipment.
     *
     * @param ShipmentInterface $shipment
     * @return $this|ShipmentSubjectInterface
     */
    public function addShipment(ShipmentInterface $shipment);

    /**
     * Removes the shipment.
     *
     * @param ShipmentInterface $shipment
     * @return $this|ShipmentSubjectInterface
     */
    public function removeShipment(ShipmentInterface $shipment);
}
