<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class ShipmentPrice
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPrice extends AbstractResource implements ShipmentPriceInterface
{
    protected ?ShipmentZoneInterface   $zone   = null;
    protected ?ShipmentMethodInterface $method = null;
    protected Decimal                  $weight;
    protected Decimal                  $netPrice;

    public function __construct()
    {
        $this->weight = new Decimal(0);
        $this->netPrice = new Decimal(0);
    }

    public function __toString(): string
    {
        if ($this->zone && $this->method) {
            return sprintf('%s / %s (%s kg)', $this->zone, $this->method, $this->weight->toFixed(3));
        }

        return 'New shipment price';
    }

    public function getZone(): ?ShipmentZoneInterface
    {
        return $this->zone;
    }

    public function setZone(?ShipmentZoneInterface $zone): ShipmentPriceInterface
    {
        if ($zone === $this->zone) {
            return $this;
        }

        if ($previous = $this->zone) {
            $this->zone = null;
            $previous->removePrice($this);
        }

        if ($this->zone = $zone) {
            $this->zone->addPrice($this);
        }

        return $this;
    }

    public function getMethod(): ?ShipmentMethodInterface
    {
        return $this->method;
    }

    public function setMethod(?ShipmentMethodInterface $method): ShipmentPriceInterface
    {
        if ($method === $this->method) {
            return $this;
        }

        if ($previous = $this->method) {
            $this->method = null;
            $previous->removePrice($this);
        }

        if ($this->method = $method) {
            $this->method->addPrice($this);
        }

        return $this;
    }

    public function getWeight(): Decimal
    {
        return $this->weight;
    }

    public function setWeight(Decimal $weight): ShipmentPriceInterface
    {
        $this->weight = $weight;

        return $this;
    }

    public function getNetPrice(): Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(Decimal $price): ShipmentPriceInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
