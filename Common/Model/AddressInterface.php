<?php

namespace Ekyna\Component\Commerce\Common\Model;

use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface AddressInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AddressInterface extends ResourceInterface, IdentityInterface
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
     * @param StateInterface $state
     * @return $this|AddressInterface
     */
    public function setState(StateInterface $state = null);

    /**
     * Returns the phone.
     *
     * @return \libphonenumber\PhoneNumber|string
     */
    public function getPhone();

    /**
     * Sets the phone.
     *
     * @param mixed $phone
     *
     * @return $this|AddressInterface
     */
    public function setPhone($phone);

    /**
     * Returns the mobile.
     *
     * @return \libphonenumber\PhoneNumber|string
     */
    public function getMobile();

    /**
     * Sets the mobile.
     *
     * @param string $mobile
     *
     * @return $this|AddressInterface
     */
    public function setMobile($mobile);
}
