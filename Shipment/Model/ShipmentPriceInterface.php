<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ShipmentPrice
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentPriceInterface extends ResourceInterface
{
    /**
     * Returns the zone.
     *
     * @return ShipmentZoneInterface
     */
    public function getZone();

    /**
     * Sets the zone.
     *
     * @param ShipmentZoneInterface $zone
     *
     * @return $this|ShipmentPriceInterface
     */
    public function setZone(ShipmentZoneInterface $zone = null);

    /**
     * Returns the method.
     *
     * @return ShipmentMethodInterface
     */
    public function getMethod();

    /**
     * Sets the method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentPriceInterface
     */
    public function setMethod(ShipmentMethodInterface $method = null);

    /**
     * Returns the weight (kilograms).
     *
     * @return float
     */
    public function getWeight();

    /**
     * Sets the weight (kilograms).
     *
     * @param float $weight
     *
     * @return $this|ShipmentPriceInterface
     */
    public function setWeight($weight);

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return $this|ShipmentPriceInterface
     */
    public function setNetPrice($price);
}
