<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;

/**
 * Class ShipmentPrice
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentPrice implements ShipmentPriceInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ShipmentZoneInterface
     */
    protected $zone;

    /**
     * @var ShipmentMethodInterface
     */
    protected $method;

    /**
     * @var float
     */
    protected $weight;

    /**
     * @var float
     */
    protected $netPrice;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->weight = 0.;
        $this->netPrice = 0.;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->zone && $this->method) {
            return sprintf('%s / %s (%s kg)', $this->zone, $this->method, round($this->weight, 2));
        }

        return 'New shipment price';
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getZone(): ?ShipmentZoneInterface
    {
        return $this->zone;
    }

    /**
     * @inheritdoc
     */
    public function setZone(ShipmentZoneInterface $zone = null): ShipmentPriceInterface
    {
        if ($zone !== $this->zone) {
            if ($previous = $this->zone) {
                $this->zone = null;
                $previous->removePrice($this);
            }

            if ($this->zone = $zone) {
                $this->zone->addPrice($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethod(): ?ShipmentMethodInterface
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function setMethod(ShipmentMethodInterface $method = null): ShipmentPriceInterface
    {
        if ($method !== $this->method) {
            if ($previous = $this->method) {
                $this->method = null;
                $previous->removePrice($this);
            }

            if ($this->method = $method) {
                $this->method->addPrice($this);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
     */
    public function setWeight(float $weight): ShipmentPriceInterface
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice(): float
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice(float $price): ShipmentPriceInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
