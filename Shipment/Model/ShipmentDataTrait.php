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
     * @var ShipmentMethodInterface
     * TODO rename to shipmentMethod
     */
    protected $preferredShipmentMethod;

    /**
     * @var float
     */
    protected $weightTotal;

    /**
     * @var float
     */
    protected $shipmentAmount;


    /**
     * Initializes the shipment data.
     */
    protected function initializeShipmentData()
    {
        $this->weightTotal = 0;
        $this->shipmentAmount = 0;
    }

    /**
     * Returns the preferred shipment method.
     *
     * @return ShipmentMethodInterface
     */
    public function getPreferredShipmentMethod()
    {
        return $this->preferredShipmentMethod;
    }

    /**
     * Sets the preferred shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentDataInterface
     */
    public function setPreferredShipmentMethod(ShipmentMethodInterface $method = null)
    {
        $this->preferredShipmentMethod = $method;

        return $this;
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
}
