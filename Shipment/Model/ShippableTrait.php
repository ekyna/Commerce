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
     * @var float
     */
    protected $shipmentWeight;

    /**
     * @var string
     */
    protected $shipmentLabel;

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
    protected function initializeShippable(): void
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
    public function getWeightTotal(): float
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
    public function setWeightTotal(float $total): ShippableInterface
    {
        $this->weightTotal = $total;

        return $this;
    }

    /**
     * Returns the preferred shipment method.
     *
     * @return ShipmentMethodInterface
     */
    public function getShipmentMethod(): ?ShipmentMethodInterface
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
    public function setShipmentMethod(ShipmentMethodInterface $method = null): ShippableInterface
    {
        $this->shipmentMethod = $method;

        return $this;
    }

    /**
     * Returns the shipment amount.
     *
     * @return float
     */
    public function getShipmentAmount(): float
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
    public function setShipmentAmount(float $amount): ShippableInterface
    {
        $this->shipmentAmount = $amount;

        return $this;
    }

    /**
     * Returns the shipment weight.
     *
     * @return float
     */
    public function getShipmentWeight(): ?float
    {
        return $this->shipmentWeight;
    }

    /**
     * Sets the shipment weight.
     *
     * @param float $weight
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentWeight(float $weight = null): ShippableInterface
    {
        $this->shipmentWeight = $weight;

        return $this;
    }

    /**
     * Returns the shipment label.
     *
     * @return string
     */
    public function getShipmentLabel(): ?string
    {
        return $this->shipmentLabel;
    }

    /**
     * Sets the shipment label.
     *
     * @param string $label
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentLabel(string $label = null): ShippableInterface
    {
        $this->shipmentLabel = $label;

        return $this;
    }

    /**
     * Returns whether auto shipping is enabled.
     *
     * @return bool
     */
    public function isAutoShipping(): bool
    {
        return $this->autoShipping;
    }

    /**
     * Sets whether auto shipping is enabled.
     *
     * @param bool $auto
     *
     * @return $this|ShippableInterface
     */
    public function setAutoShipping(bool $auto): ShippableInterface
    {
        $this->autoShipping = $auto;

        return $this;
    }

    /**
     * Returns the relay point.
     *
     * @return RelayPointInterface
     */
    public function getRelayPoint(): ?RelayPointInterface
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
    public function setRelayPoint(RelayPointInterface $relayPoint = null): ShippableInterface
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }
}
