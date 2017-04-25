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
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return '#' . $this->getId();
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @inheritdoc
     */
    public function setZone(ShipmentZoneInterface $zone = null)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @inheritdoc
     */
    public function setMethod(ShipmentMethodInterface $method = null)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @inheritdoc
     */
    public function setWeight($weight)
    {
        $this->weight = (float)$weight;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($price)
    {
        $this->netPrice = (float)$price;

        return $this;
    }
}
