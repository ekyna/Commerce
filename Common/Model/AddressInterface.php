<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Model;

use libphonenumber\PhoneNumber;

/**
 * Interface AddressInterface
 * @package Ekyna\Component\Commerce\Common\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface AddressInterface extends IdentityInterface
{
    public function getCompany(): ?string;

    public function setCompany(?string $company): AddressInterface;

    public function getStreet(): ?string;

    public function setStreet(?string $street): AddressInterface;

    public function getComplement(): ?string;

    public function setComplement(?string $complement): AddressInterface;

    public function getSupplement(): ?string;

    public function setSupplement(?string $supplement): AddressInterface;

    public function getExtra(): ?string;

    public function setExtra(?string $extra): AddressInterface;

    public function getPostalCode(): ?string;

    public function setPostalCode(?string $postalCode): AddressInterface;

    public function getCity(): ?string;

    public function setCity(?string $city): AddressInterface;

    public function getCountry(): ?CountryInterface;

    public function setCountry(?CountryInterface $country): AddressInterface;

    public function getState(): ?StateInterface;

    public function setState(?StateInterface $state): AddressInterface;

    public function getPhone(): ?PhoneNumber;

    public function setPhone(?PhoneNumber $phone): AddressInterface;

    public function getMobile(): ?PhoneNumber;

    public function setMobile(?PhoneNumber $mobile): AddressInterface;

    public function getDigicode1(): ?string;

    public function setDigicode1(?string $digicode1): AddressInterface;

    public function getDigicode2(): ?string;

    public function setDigicode2(?string $digicode2): AddressInterface;

    public function getIntercom(): ?string;

    public function setIntercom(?string $intercom): AddressInterface;

    public function getLongitude(): ?string;

    public function setLongitude(?string $longitude): AddressInterface;

    public function getLatitude(): ?string;

    public function setLatitude(?string $latitude): AddressInterface;

    /**
     * Returns whether this address can be considered as empty.
     */
    public function isEmpty(): bool;
}
