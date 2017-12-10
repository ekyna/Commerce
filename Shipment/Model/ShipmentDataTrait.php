<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Trait ShipmentDataTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ShipmentDataTrait
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
     * @var string
     */
    protected $relayPoint;


    /**
     * Initializes the shipment data.
     */
    protected function initializeShipmentData()
    {
        $this->weightTotal = 0;
        $this->shipmentAmount = 0;
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
     * @return $this|ShipmentDataInterface
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
     * @return $this|ShipmentDataInterface
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
     * @return $this|ShipmentDataInterface
     */
    public function setShipmentAmount($amount)
    {
        $this->shipmentAmount = $amount;

        return $this;
    }

    /**
     * Returns the relay point identifier.
     *
     * @return string
     */
    public function getRelayPoint()
    {
        return $this->relayPoint;
    }

    /**
     * Sets the relay point identifier.
     *
     * @param string $relayPoint
     *
     * @return $this|ShipmentDataInterface
     */
    public function setRelayPoint($relayPoint)
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }
}
