<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface TaxRuleInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxRuleInterface extends ResourceInterface
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
     * @return $this|TaxRuleInterface
     */
    public function setName($name);

    /**
     * Returns the customer.
     *
     * @return bool
     */
    public function isCustomer();

    /**
     * Sets the customer.
     *
     * @param bool $customer
     *
     * @return $this|TaxRuleInterface
     */
    public function setCustomer($customer);

    /**
     * Returns the business.
     *
     * @return bool
     */
    public function isBusiness();

    /**
     * Sets the business.
     *
     * @param bool $business
     *
     * @return $this|TaxRuleInterface
     */
    public function setBusiness($business);

    /**
     * Returns whether the tax rule has countries.
     *
     * @return bool
     */
    public function hasCountries();

    /**
     * Returns the countries.
     *
     * @return ArrayCollection|CountryInterface[]
     */
    public function getCountries();

    /**
     * Returns whether the tax rule has the given country.
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
     * @return $this|TaxRuleInterface
     */
    public function addCountry(CountryInterface $country);

    /**
     * Removes the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|TaxRuleInterface
     */
    public function removeCountry(CountryInterface $country);

    /**
     * Sets the countries.
     *
     * @param CountryInterface[] $countries
     *
     * @return $this|TaxRuleInterface
     */
    public function setCountries(array $countries);

    /**
     * Returns whether the tax rule has taxes.
     *
     * @return bool
     */
    public function hasTaxes();

    /**
     * Returns the taxes.
     *
     * @return ArrayCollection|TaxInterface[]
     */
    public function getTaxes();

    /**
     * Returns whether the tax rule has the given tax.
     *
     * @param TaxInterface $tax
     *
     * @return bool
     */
    public function hasTax(TaxInterface $tax);

    /**
     * Adds the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|TaxRuleInterface
     */
    public function addTax(TaxInterface $tax);

    /**
     * Removes the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|TaxRuleInterface
     */
    public function removeTax(TaxInterface $tax);

    /**
     * Sets the taxes.
     *
     * @param TaxInterface[] $taxes
     *
     * @return $this|TaxRuleInterface
     */
    public function setTaxes(array $taxes);

    /**
     * Returns the notices.
     *
     * @return array
     */
    public function getNotices();

    /**
     * Sets the notices.
     *
     * @param array $notices
     *
     * @return $this|TaxRuleInterface
     */
    public function setNotices(array $notices);

    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Sets the priority.
     *
     * @param int $priority
     *
     * @return $this|TaxRuleInterface
     */
    public function setPriority($priority);

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position.
     *
     * @param int $position
     *
     * @return $this|TaxRuleInterface
     */
    public function setPosition($position);
}
