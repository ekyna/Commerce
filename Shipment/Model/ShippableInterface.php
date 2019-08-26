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
    public function getWeightTotal(): float;

    /**
     * Sets the weight total (kilograms).
     *
     * @param float $total
     *
     * @return $this|ShippableInterface
     */
    public function setWeightTotal(float $total): ShippableInterface;

    /**
     * Returns the shipment method.
     *
     * @return ShipmentMethodInterface
     */
    public function getShipmentMethod(): ?ShipmentMethodInterface;

    /**
     * Sets the shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentMethod(ShipmentMethodInterface $method = null): ShippableInterface;

    /**
     * Returns the shipment amount.
     *
     * @return float
     */
    public function getShipmentAmount(): ?float;

    /**
     * Sets the shipment amount.
     *
     * @param float $amount
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentAmount(float $amount = null): ShippableInterface;

    /**
     * Returns the shipment weight.
     *
     * @return float
     */
    public function getShipmentWeight(): ?float;

    /**
     * Sets the shipment weight.
     *
     * @param float $weight
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentWeight(float $weight = null): ShippableInterface;

    /**
     * Returns the shipment label.
     *
     * @return string
     */
    public function getShipmentLabel(): ?string;

    /**
     * Sets the shipment label.
     *
     * @param string $label
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentLabel(string $label = null): ShippableInterface;

    /**
     * Returns whether auto shipping is enabled.
     *
     * @return bool
     */
    public function isAutoShipping(): bool;

    /**
     * Sets whether auto shipping is enabled.
     *
     * @param bool $auto
     *
     * @return $this|ShippableInterface
     */
    public function setAutoShipping(bool $auto): ShippableInterface;

    /**
     * Returns the relay point.
     *
     * @return RelayPointInterface
     */
    public function getRelayPoint(): ?RelayPointInterface;

    /**
     * Sets the relay point.
     *
     * @param RelayPointInterface $relayPoint
     *
     * @return $this|ShippableInterface
     */
    public function setRelayPoint(RelayPointInterface $relayPoint = null): ShippableInterface;
}
