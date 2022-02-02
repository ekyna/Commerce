<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentPriceInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentZoneInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class ShipmentZone
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentZone extends AbstractResource implements ShipmentZoneInterface
{
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
    public function __toString(): string
    {
        return $this->name ?: 'New shipment zone';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @inheritDoc
     */
    public function hasCountries()
    {
        return 0 < $this->countries->count();
    }

    /**
     * @inheritDoc
     */
    public function hasCountry(CountryInterface $country)
    {
        return $this->countries->contains($country);
    }

    /**
     * @inheritDoc
     */
    public function addCountry(CountryInterface $country)
    {
        if (!$this->hasCountry($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeCountry(CountryInterface $country)
    {
        if ($this->hasCountry($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @inheritDoc
     */
    public function hasPrices()
    {
        return 0 < $this->prices->count();
    }

    /**
     * @inheritDoc
     */
    public function hasPrice(ShipmentPriceInterface $price)
    {
        return $this->prices->contains($price);
    }

    /**
     * @inheritDoc
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
     * @inheritDoc
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
