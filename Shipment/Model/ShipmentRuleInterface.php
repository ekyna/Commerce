<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface ShipmentRuleInterface
 * @package Ekyna\Component\Commerce\Shipment\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShipmentRuleInterface extends ResourceInterface
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
     * @return $this|ShipmentRuleInterface
     */
    public function setName($name);

    /**
     * Returns the methods.
     *
     * @return ArrayCollection|ShipmentMethodInterface[]
     */
    public function getMethods();

    /**
     * Adds the shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentRuleInterface
     */
    public function addMethod(ShipmentMethodInterface $method);

    /**
     * Removes the shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentRuleInterface
     */
    public function removeMethod(ShipmentMethodInterface $method);

    /**
     * Returns the countries.
     *
     * @return ArrayCollection|CountryInterface[]
     */
    public function getCountries();

    /**
     * Adds the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ShipmentRuleInterface
     */
    public function addCountry(CountryInterface $country);

    /**
     * Removes the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ShipmentRuleInterface
     */
    public function removeCountry(CountryInterface $country);

    /**
     * Returns the customer groups.
     *
     * @return ArrayCollection|ShipmentMethodInterface[]
     */
    public function getCustomerGroups();

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|ShipmentRuleInterface
     */
    public function addCustomerGroup(CustomerGroupInterface $group);

    /**
     * Removes the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|ShipmentRuleInterface
     */
    public function removeCustomerGroup(CustomerGroupInterface $group);

    /**
     * Returns the sale base total.
     *
     * @return float
     */
    public function getBaseTotal();

    /**
     * Sets the sale base total.
     *
     * @param float $total
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setBaseTotal($total);

    /**
     * Returns the vat mode.
     *
     * @return string
     */
    public function getVatMode();

    /**
     * Sets the vat mode.
     *
     * @param string $vatMode
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setVatMode($vatMode);

    /**
     * Returns the "start at" datetime.
     *
     * @return \DateTime
     */
    public function getStartAt();

    /**
     * Sets the "start at" datetime.
     *
     * @param \DateTime $date
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setStartAt(\DateTime $date = null);

    /**
     * Returns the "end at" datetime.
     *
     * @return \DateTime
     */
    public function getEndAt();

    /**
     * Sets the "end at" datetime.
     *
     * @param \DateTime $date
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setEndAt(\DateTime $date = null);
}
