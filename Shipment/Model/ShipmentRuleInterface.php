<?php

namespace Ekyna\Component\Commerce\Shipment\Model;

use DateTime;
use Doctrine\Common\Collections\Collection;
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
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setName(string $name): ShipmentRuleInterface;

    /**
     * Returns the methods.
     *
     * @return Collection|ShipmentMethodInterface[]
     */
    public function getMethods(): Collection;

    /**
     * Adds the shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentRuleInterface
     */
    public function addMethod(ShipmentMethodInterface $method): ShipmentRuleInterface;

    /**
     * Removes the shipment method.
     *
     * @param ShipmentMethodInterface $method
     *
     * @return $this|ShipmentRuleInterface
     */
    public function removeMethod(ShipmentMethodInterface $method): ShipmentRuleInterface;

    /**
     * Returns the countries.
     *
     * @return Collection|CountryInterface[]
     */
    public function getCountries(): Collection;

    /**
     * Adds the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ShipmentRuleInterface
     */
    public function addCountry(CountryInterface $country): ShipmentRuleInterface;

    /**
     * Removes the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|ShipmentRuleInterface
     */
    public function removeCountry(CountryInterface $country): ShipmentRuleInterface;

    /**
     * Returns the customer groups.
     *
     * @return Collection|ShipmentMethodInterface[]
     */
    public function getCustomerGroups(): Collection;

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|ShipmentRuleInterface
     */
    public function addCustomerGroup(CustomerGroupInterface $group): ShipmentRuleInterface;

    /**
     * Removes the customer group.
     *
     * @param CustomerGroupInterface $group
     *
     * @return $this|ShipmentRuleInterface
     */
    public function removeCustomerGroup(CustomerGroupInterface $group): ShipmentRuleInterface;

    /**
     * Returns the sale base total.
     *
     * @return float
     */
    public function getBaseTotal(): float;

    /**
     * Sets the sale base total.
     *
     * @param float $total
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setBaseTotal(float $total): ShipmentRuleInterface;

    /**
     * Returns the vat mode.
     *
     * @return string
     */
    public function getVatMode(): string;

    /**
     * Sets the vat mode.
     *
     * @param string $mode
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setVatMode(string $mode): ShipmentRuleInterface;

    /**
     * Returns the "start at" datetime.
     *
     * @return DateTime|null
     */
    public function getStartAt(): ?DateTime;

    /**
     * Sets the "start at" datetime.
     *
     * @param DateTime $date
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setStartAt(DateTime $date = null): ShipmentRuleInterface;

    /**
     * Returns the "end at" datetime.
     *
     * @return DateTime|null
     */
    public function getEndAt(): ?DateTime;

    /**
     * Sets the "end at" datetime.
     *
     * @param DateTime $date
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setEndAt(DateTime $date = null): ShipmentRuleInterface;

    /**
     * Returns the net price.
     *
     * @return float
     */
    public function getNetPrice(): float;

    /**
     * Sets the net price.
     *
     * @param float $price
     *
     * @return $this|ShipmentRuleInterface
     */
    public function setNetPrice(float $price): ShipmentRuleInterface;
}
