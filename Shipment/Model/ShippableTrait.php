<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Model;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\Incoterm;

/**
 * Trait ShippableTrait
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait ShippableTrait
{
    protected Decimal                  $weightTotal;
    protected ?ShipmentMethodInterface $shipmentMethod = null;
    protected Decimal                  $shipmentAmount;
    protected ?Decimal                 $shipmentWeight = null;
    protected ?string                  $shipmentLabel  = null;
    protected ?Incoterm                $incoterm       = null;
    protected bool                     $autoShipping;
    protected ?RelayPointInterface     $relayPoint     = null;


    /**
     * Initializes the shipment data.
     */
    protected function initializeShippable(): void
    {
        $this->weightTotal = new Decimal(0);
        $this->shipmentAmount = new Decimal(0);
        $this->autoShipping = true;
        $this->incoterm = Incoterm::DAP;
    }

    public function getWeightTotal(): Decimal
    {
        return $this->weightTotal;
    }

    public function setWeightTotal(Decimal $total): ShippableInterface
    {
        $this->weightTotal = $total;

        return $this;
    }

    public function getShipmentMethod(): ?ShipmentMethodInterface
    {
        return $this->shipmentMethod;
    }

    public function setShipmentMethod(?ShipmentMethodInterface $method): ShippableInterface
    {
        $this->shipmentMethod = $method;

        return $this;
    }

    public function getShipmentAmount(): Decimal
    {
        return $this->shipmentAmount;
    }

    public function setShipmentAmount(Decimal $amount): ShippableInterface
    {
        $this->shipmentAmount = $amount;

        return $this;
    }

    public function getShipmentWeight(): ?Decimal
    {
        return $this->shipmentWeight;
    }

    public function setShipmentWeight(?Decimal $weight): ShippableInterface
    {
        $this->shipmentWeight = $weight;

        return $this;
    }

    public function getShipmentLabel(): ?string
    {
        return $this->shipmentLabel;
    }

    public function setShipmentLabel(?string $label): ShippableInterface
    {
        $this->shipmentLabel = $label;

        return $this;
    }

    public function getIncoterm(): ?Incoterm
    {
        return $this->incoterm;
    }

    public function setIncoterm(?Incoterm $incoterm): ShippableInterface
    {
        $this->incoterm = $incoterm;

        return $this;
    }

    public function isAutoShipping(): bool
    {
        return $this->autoShipping;
    }

    public function setAutoShipping(bool $auto): ShippableInterface
    {
        $this->autoShipping = $auto;

        return $this;
    }

    public function getRelayPoint(): ?RelayPointInterface
    {
        return $this->relayPoint;
    }

    public function setRelayPoint(?RelayPointInterface $relayPoint): ShippableInterface
    {
        $this->relayPoint = $relayPoint;

        return $this;
    }
}
