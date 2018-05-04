<?php

namespace Ekyna\Component\Commerce\Common\Model;

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
    public function getCompany();

    /**
     * Sets the company.
     *
     * @param string $company
     *
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
     *
     * @return $this|AddressInterface
     */
    public function setStreet($street);

    /**
     * Returns the complement.
     *
     * @return string
     */
    public function getComplement();

    /**
     * Sets the complement.
     *
     * @param string $complement
     *
     * @return $this|AddressInterface
     */
    public function setComplement($complement);

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
     *
     * @return $this|AddressInterface
     */
    public function setSupplement($supplement);

    /**
     * Returns the extra.
     *
     * @return string
     */
    public function getExtra();

    /**
     * Sets the extra.
     *
     * @param string $extra
     */
    public function setExtra($extra);

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
     *
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
     *
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
     * @return StateInterface
     */
    public function getState();

    /**
     * Sets the state.
     *
     * @param StateInterface $state
     *
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

    /**
     * Returns the digicode 1.
     *
     * @return string
     */
    public function getDigicode1();

    /**
     * Sets the digicode 1.
     *
     * @param string $digicode1
     */
    public function setDigicode1($digicode1);

    /**
     * Returns the digicode 2.
     *
     * @return string
     */
    public function getDigicode2();

    /**
     * Sets the digicode 2.
     *
     * @param string $digicode2
     */
    public function setDigicode2($digicode2);

    /**
     * Returns the intercom.
     *
     * @return string
     */
    public function getIntercom();

    /**
     * Sets the intercom.
     *
     * @param string $intercom
     */
    public function setIntercom($intercom);

    /**
     * Returns the longitude.
     *
     * @return float
     */
    public function getLongitude();

    /**
     * Sets the longitude.
     *
     * @param float $longitude
     *
     * @return $this|AddressInterface
     */
    public function setLongitude($longitude);

    /**
     * Returns the latitude.
     *
     * @return float
     */
    public function getLatitude();

    /**
     * Sets the latitude.
     *
     * @param float $latitude
     *
     * @return $this|AddressInterface
     */
    public function setLatitude($latitude);
}
