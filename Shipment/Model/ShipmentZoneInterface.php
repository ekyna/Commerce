<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Class ShipmentZone
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentZoneInterface extends ResourceInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|ShipmentZoneInterface
     */
    public function setName($name);

    /**
     * Returns the countries.
     *
     * @return ArrayCollection|CountryInterface[]
     */
    public function getCountries();

    /**
     * Returns whether or not the zone has at least one country.
     *
     * @return bool
     */
    public function hasCountries();

    /**
     * Returns whether or not the zone has the given country.
     *
     * @param CountryInterface $country
     *
     * @return bool
     */
    public function hasCountry(CountryInterface $country);

    /**
     * Adds the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ShipmentZoneInterface
     */
    public function addCountry(CountryInterface $country);

    /**
     * Removes the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ShipmentZoneInterface
     */
    public function removeCountry(CountryInterface $country);

    /**
     * Returns the prices.
     *
     * @return ArrayCollection|ShipmentPriceInterface[]
     */
    public function getPrices();

    /**
     * Returns whether or not the zone has at least one price.
     *
     * @return bool
     */
    public function hasPrices();

    /**
     * Returns whether or not the zone has the given price.
     *
     * @param ShipmentPriceInterface $price
     *
     * @return bool
     */
    public function hasPrice(ShipmentPriceInterface $price);

    /**
     * Adds the price.
     *
     * @param ShipmentPriceInterface $price
     *
     * @return $this|ShipmentZoneInterface
     */
    public function addPrice(ShipmentPriceInterface $price);

    /**
     * Removes the price.
     *
     * @param ShipmentPriceInterface $price
     *
     * @return $this|ShipmentZoneInterface
     */
    public function removePrice(ShipmentPriceInterface $price);
}
