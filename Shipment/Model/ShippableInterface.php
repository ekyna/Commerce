<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Incoterm;

/**
 * Interface ShippableInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShippableInterface
{
    /**
     * Returns the weight total (kilograms).
     */
    public function getWeightTotal(): Decimal;

    /**
     * Sets the weight total (kilograms).
     *
     * @return $this|ShippableInterface
     */
    public function setWeightTotal(Decimal $total): ShippableInterface;

    /**
     * Sets the preferred shipment method.
     */
    public function getShipmentMethod(): ?ShipmentMethodInterface;

    /**
     * Returns the preferred shipment method.
     *
     * @return $this|ShippableInterface
     */
    public function setShipmentMethod(?ShipmentMethodInterface $method): ShippableInterface;

    public function getShipmentAmount(): Decimal;

    /**
     * @return $this|ShippableInterface
     */
    public function setShipmentAmount(Decimal $amount): ShippableInterface;

    /**
     * Returns the shipment weight.
     */
    public function getShipmentWeight(): ?Decimal;

    /**
     * @return $this|ShippableInterface
     */
    public function setShipmentWeight(?Decimal $weight): ShippableInterface;

    public function getShipmentLabel(): ?string;

    /**
     * @return $this|ShippableInterface
     */
    public function setShipmentLabel(?string $label): ShippableInterface;

    public function getIncoterm(): ?Incoterm;

    /**
     * @return $this|ShippableInterface
     */
    public function setIncoterm(?Incoterm $incoterm): ShippableInterface;

    /**
     * Returns whether auto shipping is enabled.
     */
    public function isAutoShipping(): bool;

    /**
     * Sets whether auto shipping is enabled.
     *
     * @return $this|ShippableInterface
     */
    public function setAutoShipping(bool $auto): ShippableInterface;

    public function getRelayPoint(): ?RelayPointInterface;

    /**
     * @return $this|ShippableInterface
     */
    public function setRelayPoint(?RelayPointInterface $relayPoint): ShippableInterface;
}
