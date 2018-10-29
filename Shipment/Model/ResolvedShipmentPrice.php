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
    private $taxes = [];


    /**
     * Constructor.
     *
     * @param ShipmentMethodInterface $method
     * @param float                   $weight
     * @param float                   $price
     */
    public function __construct(ShipmentMethodInterface $method, float $weight, float $price = 0)
    {
        $this->method = $method;
        $this->weight = $weight;
        $this->price = $price;
    }

    /**
     * Returns the method.
     *
     * @return ShipmentMethodInterface
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the weight.
     *
     * @return float
     */
    public function getWeight()
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
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Returns the price.
     *
     * @return float
     */
    public function getPrice()
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
    public function setTaxes(array $taxes)
    {
        $this->taxes = $taxes;

        return $this;
    }

    /**
     * Returns the taxes.
     *
     * @return float[]
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * Returns the free.
     *
     * @return bool
     */
    public function isFree()
    {
        return 0 === bccomp($this->price, 0, 3);
    }
}
