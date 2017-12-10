<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Interface ShipmentDataInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentDataInterface
{
    /**
     * Returns the weight total (kilograms).
     *
     * @return float
     */
    public function getWeightTotal();

    /**
     * Sets the weight total (kilograms).
     *
     * @param float $total
     *
     * @return $this|ShipmentDataInterface
     */
    public function setWeightTotal($total);

    /**
     * Returns the shipment method.
     *
     * @return ShipmentMethodInterface
     */
    public function getShipmentMethod();

    /**
     * Sets the shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentDataInterface
     */
    public function setShipmentMethod(ShipmentMethodInterface $method = null);

    /**
     * Returns the shipment amount.
     *
     * @return float
     */
    public function getShipmentAmount();

    /**
     * Sets the shipment amount.
     *
     * @param float $amount
     *
     * @return $this|ShipmentDataInterface
     */
    public function setShipmentAmount($amount);

    /**
     * Returns the relay point identifier.
     *
     * @return string
     */
    public function getRelayPoint();

    /**
     * Sets the relay point identifier.
     *
     * @param string $relayPoint
     *
     * @return $this|ShipmentDataInterface
     */
    public function setRelayPoint($relayPoint);
}
