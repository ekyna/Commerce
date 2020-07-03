<?php

namespace Ekyna\Component\Commerce\Common\Model;

use libphonenumber\PhoneNumber;

/**
 * Interface AddressInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AddressInterface extends IdentityInterface
{
    /**
     * Returns the company.
     *
     * @return string
     */
    public function getCompany(): ?string;

    /**
     * Sets the company.
     *
     * @param string $company
     *
     * @return $this|AddressInterface
     */
    public function setCompany(string $company = null): AddressInterface;

    /**
     * Returns the street.
     *
     * @return string
     */
    public function getStreet(): ?string;

    /**
     * Sets the street.
     *
     * @param string $street
     *
     * @return $this|AddressInterface
     */
    public function setStreet(string $street): AddressInterface;

    /**
     * Returns the complement.
     *
     * @return string
     */
    public function getComplement(): ?string;

    /**
     * Sets the complement.
     *
     * @param string $complement
     *
     * @return $this|AddressInterface
     */
    public function setComplement(string $complement = null): AddressInterface;

    /**
     * Returns the supplement.
     *
     * @return string
     */
    public function getSupplement(): ?string;

    /**
     * Sets the supplement.
     *
     * @param string $supplement
     *
     * @return $this|AddressInterface
     */
    public function setSupplement(string $supplement = null): AddressInterface;

    /**
     * Returns the extra.
     *
     * @return string
     */
    public function getExtra(): ?string;

    /**
     * Sets the extra.
     *
     * @param string $extra
     *
     * @return $this|AddressInterface
     */
    public function setExtra(string $extra = null): AddressInterface;

    /**
     * Returns the postalCode.
     *
     * @return string
     */
    public function getPostalCode(): ?string;

    /**
     * Sets the postalCode.
     *
     * @param string $postalCode
     *
     * @return $this|AddressInterface
     */
    public function setPostalCode(string $postalCode): AddressInterface;

    /**
     * Returns the city.
     *
     * @return string
     */
    public function getCity(): ?string;

    /**
     * Sets the city.
     *
     * @param string $city
     *
     * @return $this|AddressInterface
     */
    public function setCity(string $city): AddressInterface;

    /**
     * Returns the country.
     *
     * @return CountryInterface
     */
    public function getCountry(): ?CountryInterface;

    /**
     * Sets the country.
     *
     * @param CountryInterface $country
     *
     * @return $this|AddressInterface
     */
    public function setCountry(CountryInterface $country): AddressInterface;

    /**
     * Returns the state.
     *
     * @return StateInterface
     */
    public function getState(): ?StateInterface;

    /**
     * Sets the state.
     *
     * @param StateInterface $state
     *
     * @return $this|AddressInterface
     */
    public function setState(StateInterface $state = null): AddressInterface;

    /**
     * Returns the phone.
     *
     * @return PhoneNumber|null
     */
    public function getPhone(): ?PhoneNumber;

    /**
     * Sets the phone.
     *
     * @param PhoneNumber $phone
     *
     * @return $this|AddressInterface
     */
    public function setPhone(PhoneNumber $phone = null);

    /**
     * Returns the mobile.
     *
     * @return PhoneNumber|null
     */
    public function getMobile(): ?PhoneNumber;

    /**
     * Sets the mobile.
     *
     * @param PhoneNumber $mobile
     *
     * @return $this|AddressInterface
     */
    public function setMobile(PhoneNumber $mobile = null);

    /**
     * Returns the digicode 1.
     *
     * @return string
     */
    public function getDigicode1(): ?string;

    /**
     * Sets the digicode 1.
     *
     * @param string $digicode1
     *
     * @return $this|AddressInterface
     */
    public function setDigicode1(string $digicode1 = null): AddressInterface;

    /**
     * Returns the digicode 2.
     *
     * @return string
     */
    public function getDigicode2(): ?string;

    /**
     * Sets the digicode 2.
     *
     * @param string $digicode2
     *
     * @return $this|AddressInterface
     */
    public function setDigicode2(string $digicode2 = null): AddressInterface;

    /**
     * Returns the intercom.
     *
     * @return string
     */
    public function getIntercom(): ?string;

    /**
     * Sets the intercom.
     *
     * @param string $intercom
     *
     * @return $this|AddressInterface
     */
    public function setIntercom(string $intercom = null): AddressInterface;

    /**
     * Returns the longitude.
     *
     * @return float
     */
    public function getLongitude(): ?float;

    /**
     * Sets the longitude.
     *
     * @param float $longitude
     *
     * @return $this|AddressInterface
     */
    public function setLongitude(float $longitude = null): AddressInterface;

    /**
     * Returns the latitude.
     *
     * @return float
     */
    public function getLatitude(): ?float;

    /**
     * Sets the latitude.
     *
     * @param float $latitude
     *
     * @return $this|AddressInterface
     */
    public function setLatitude(float $latitude): AddressInterface;

    /**
     * Returns whether this address can be considered as empty.
     *
     * @return bool
     */
    public function isEmpty(): bool;
}
