<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

/**
 * Class ResolvedShipmentPrice
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ResolvedShipmentPrice
{
    /**
     * @var ShipmentMethodInterface
     */
    private $method;

    /**
     * @var float
     */
    private $weight;

    /**
     * @var float
     */
    private $price;

    /**
     * @var float[]
     */
    private $taxes;


    /**
     * Constructor.
     *
     * @param ShipmentMethodInterface $method
     * @param float                   $weight
     * @param float                   $price
     */
    public function __construct(ShipmentMethodInterface $method, float $weight, float $price = 0.)
    {
        $this->method = $method;
        $this->weight = $weight;
        $this->price = $price;
        $this->taxes = [];
    }

    /**
     * Returns the method.
     *
     * @return ShipmentMethodInterface
     */
    public function getMethod(): ShipmentMethodInterface
    {
        return $this->method;
    }

    /**
     * Returns the weight.
     *
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * Sets the price.
     *
     * @param float $price
     *
     * @return ResolvedShipmentPrice
     */
    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Returns the price.
     *
     * @return float
     */
    public function getPrice(): float
    {
        return $this->price;
    }

    /**
     * Sets the taxes.
     *
     * @param float[] $taxes
     *
     * @return ResolvedShipmentPrice
     */
    public function setTaxes(array $taxes): self
    {
        $this->taxes = $taxes;

        return $this;
    }

    /**
     * Returns the taxes.
     *
     * @return float[]
     */
    public function getTaxes(): array
    {
        return $this->taxes;
    }

    /**
     * Returns the free.
     *
     * @return bool
     */
    public function isFree(): bool
    {
        return 0 === bccomp($this->price, 0, 3);
    }
}
