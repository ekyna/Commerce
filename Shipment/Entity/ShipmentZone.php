<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;

/**
 * Class ShipmentZone
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentZone implements ShipmentZoneInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var ArrayCollection|CountryInterface[]
     */
    protected $countries;

    /**
     * @var ArrayCollection|ShipmentPriceInterface[]
     */
    protected $prices;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->countries = new ArrayCollection();
        $this->prices = new ArrayCollection();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @inheritdoc
     */
    public function hasCountries()
    {
        return 0 < $this->countries->count();
    }

    /**
     * @inheritdoc
     */
    public function hasCountry(CountryInterface $country)
    {
        return $this->countries->contains($country);
    }

    /**
     * @inheritdoc
     */
    public function addCountry(CountryInterface $country)
    {
        if (!$this->hasCountry($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCountry(CountryInterface $country)
    {
        if ($this->hasCountry($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @inheritdoc
     */
    public function hasPrices()
    {
        return 0 < $this->prices->count();
    }

    /**
     * @inheritdoc
     */
    public function hasPrice(ShipmentPriceInterface $price)
    {
        return $this->prices->contains($price);
    }

    /**
     * @inheritdoc
     */
    public function addPrice(ShipmentPriceInterface $price)
    {
        if (!$this->hasPrice($price)) {
            $this->prices->add($price);
            $price->setZone($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removePrice(ShipmentPriceInterface $price)
    {
        if ($this->hasPrice($price)) {
            $this->prices->removeElement($price);
            $price->setZone(null);
        }

        return $this;
    }
}
