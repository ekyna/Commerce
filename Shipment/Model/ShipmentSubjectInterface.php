<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class ShipmentSubjectInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentSubjectInterface extends ShipmentDataInterface
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
     * @param string $state
     *
     * @return $this|ShipmentSubjectInterface
     */
    public function setShipmentState($state);

    /**
     * Returns whether the order has shipments or not.
     *
     * @return bool
     */
    public function hasShipments();

    /**
     * Returns the shipments.
     *
     * @param bool $filter TRUE for returns, FALSE for shipments, NULL for all
     *
     * @return \Doctrine\Common\Collections\Collection|ShipmentInterface[]
     */
    public function getShipments($filter = null);

    /**
     * Returns whether the order has the shipment or not.
     *
     * @param ShipmentInterface $shipment
     *
     * @return bool
     */
    public function hasShipment(ShipmentInterface $shipment);

    /**
     * Adds the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return $this|ShipmentSubjectInterface
     */
    public function addShipment(ShipmentInterface $shipment);

    /**
     * Removes the shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return $this|ShipmentSubjectInterface
     */
    public function removeShipment(ShipmentInterface $shipment);

    /**
     * Returns the shipment date.
     *
     * @param bool $latest Whether to return the last shipment date instead of the first
     *
     * @return \DateTime|null
     */
    public function getShippedAt($latest = false);
}
