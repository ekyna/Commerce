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
     * Returns the preferred shipment method.
     *
     * @return ShipmentMethodInterface
     */
    public function getPreferredShipmentMethod();

    /**
     * Sets the preferred shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentDataInterface
     */
    public function setPreferredShipmentMethod(ShipmentMethodInterface $method = null);

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
}
