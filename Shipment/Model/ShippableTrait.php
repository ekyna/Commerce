<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Trait ShippableTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ShippableTrait
{
    /**
     * @var float
     */
    protected $weightTotal;

    /**
     * @var ShipmentMethodInterface
     */
    protected $shipmentMethod;

    /**
     * @var float
     */
    protected $shipmentAmount;

    /**
     * @var bool
     */
    protected $autoShipping;

    /**
     * @var RelayPointInterface
     */
    protected $relayPoint;


    /**
     * Initializes the shipment data.
     */
    protected function initializeShippable()
    {
        $this->weightTotal = 0;
        $this->shipmentAmount = 0;
        $this->autoShipping = true;
    }

    /**
     * Returns the weight total (kilograms).
     *
     * @return float
     */
    public function getWeightTotal()
    {
        return $this->weightTotal;
    }

    /**
     * Sets the weight total (kilograms).
     *
     * @param float $total
     *
     * @return $this|ShippableInterface
     */
    public function setWeightTotal($total)
    {
        $this->weightTotal = $total;

        return $this;
    }

    /**
     * Returns the preferred shipment method.
     *
     * @return ShipmentMethodInterface
     */
    public function getShipmentMethod()
    {
        return $this->shipmentMethod;
    }

    /**
     * Sets the preferred shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentMethod(ShipmentMethodInterface $method = null)
    {
        $this->shipmentMethod = $method;

        return $this;
    }

    /**
     * Returns the shipment amount.
     *
     * @return float
     */
    public function getShipmentAmount()
    {
        return $this->shipmentAmount;
    }

    /**
     * Sets the shipment amount.
     *
     * @param float $amount
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentAmount($amount)
    {
        $this->shipmentAmount = $amount;

        return $this;
    }

    /**
     * Returns whether auto shipping is enabled.
     *
     * @return bool
     */
    public function isAutoShipping()
    {
        return $this->autoShipping;
    }

    /**
     * Sets whether auto shipping is enabled.
     *
     * @param bool $auto
     *
     * @return ShippableTrait
     */
    public function setAutoShipping($auto)
    {
        $this->autoShipping = (bool)$auto;

        return $this;
    }

    /**
     * Returns the relay point.
     *
     * @return RelayPointInterface
     */
    public function getRelayPoint()
    {
        return $this->relayPoint;
    }

    /**
     * Sets the relay point.
     *
     * @param RelayPointInterface $relayPoint
     *
     * @return $this|ShippableInterface
     */
    public function setRelayPoint(RelayPointInterface $relayPoint = null)
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }
}
