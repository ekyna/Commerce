<?php

namespace Ekyna\Component\Commerce\Address\Model;

/**
 * Interface AddressInterface
 * @package Ekyna\Component\Commerce\Address\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AddressInterface
{
    /**
     * Returns the company.
     *
     * @return string
     */
    public function getCompany();

    /**
     * Sets the company.
     *
     * @param string $company
     * @return $this|AddressInterface
     */
    public function setCompany($company);

    /**
     * Returns the firstName.
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Sets the firstName.
     *
     * @param string $firstName
     * @return $this|AddressInterface
     */
    public function setFirstName($firstName);

    /**
     * Returns the lastName.
     *
     * @return string
     */
    public function getLastName();

    /**
     * Sets the lastName.
     *
     * @param string $lastName
     * @return $this|AddressInterface
     */
    public function setLastName($lastName);

    /**
     * Returns the street.
     *
     * @return string
     */
    public function getStreet();

    /**
     * Sets the street.
     *
     * @param string $street
     * @return $this|AddressInterface
     */
    public function setStreet($street);

    /**
     * Returns the supplement.
     *
     * @return string
     */
    public function getSupplement();

    /**
     * Sets the supplement.
     *
     * @param string $supplement
     * @return $this|AddressInterface
     */
    public function setSupplement($supplement);

    /**
     * Returns the postalCode.
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Sets the postalCode.
     *
     * @param string $postalCode
     * @return $this|AddressInterface
     */
    public function setPostalCode($postalCode);

    /**
     * Returns the city.
     *
     * @return string
     */
    public function getCity();

    /**
     * Sets the city.
     *
     * @param string $city
     * @return $this|AddressInterface
     */
    public function setCity($city);

    /**
     * Returns the country.
     *
     * @return CountryInterface
     */
    public function getCountry();

    /**
     * Sets the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|AddressInterface
     */
    public function setCountry(CountryInterface $country);

    /**
     * Returns the state.
     *
     * @return string
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param string $state
     * @return $this|AddressInterface
     */
    public function setState(StateInterface $state = null);
}
