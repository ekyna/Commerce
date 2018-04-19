<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Interface ShippableInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShippableInterface
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
     * @return $this|ShippableInterface
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
     * @return $this|ShippableInterface
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
     * @return $this|ShippableInterface
     */
    public function setShipmentAmount($amount);

    /**
     * Returns the relay point.
     *
     * @return RelayPointInterface
     */
    public function getRelayPoint();

    /**
     * Sets the relay point.
     *
     * @param RelayPointInterface $relayPoint
     *
     * @return $this|ShippableInterface
     */
    public function setRelayPoint(RelayPointInterface $relayPoint = null);
}
