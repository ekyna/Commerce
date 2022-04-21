<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model;
use libphonenumber\PhoneNumber;

/**
 * Class AbstractAddress
 * @package Ekyna\Component\Commerce\Common\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAddress implements Model\AddressInterface
{
    use Model\IdentityTrait;

    protected ?string                 $company     = null;
    protected ?string                 $street      = null;
    protected ?string                 $complement  = null;
    protected ?string                 $supplement  = null;
    protected ?string                 $extra       = null;
    protected ?string                 $postalCode  = null;
    protected ?string                 $city        = null;
    protected ?Model\CountryInterface $country     = null;
    protected ?Model\StateInterface   $state       = null;
    protected ?PhoneNumber            $phone       = null;
    protected ?PhoneNumber            $mobile      = null;
    protected ?string                 $digicode1   = null;
    protected ?string                 $digicode2   = null;
    protected ?string                 $intercom    = null;
    protected ?string                 $information = null;
    protected ?string                 $longitude   = null;
    protected ?string                 $latitude    = null;


    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        if (empty($this->street) && empty($this->postalCode) && empty($this->city)) {
            return 'New address';
        }

        return trim(sprintf('%s %s %s', $this->street, $this->postalCode, $this->city));
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): Model\AddressInterface
    {
        $this->company = $company;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): Model\AddressInterface
    {
        $this->street = $street;

        return $this;
    }

    public function getComplement(): ?string
    {
        return $this->complement;
    }

    public function setComplement(?string $complement): Model\AddressInterface
    {
        $this->complement = $complement;

        return $this;
    }

    public function getSupplement(): ?string
    {
        return $this->supplement;
    }

    public function setSupplement(?string $supplement): Model\AddressInterface
    {
        $this->supplement = $supplement;

        return $this;
    }

    public function getExtra(): ?string
    {
        return $this->extra;
    }

    public function setExtra(?string $extra): Model\AddressInterface
    {
        $this->extra = $extra;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): Model\AddressInterface
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): Model\AddressInterface
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?Model\CountryInterface
    {
        return $this->country;
    }

    public function setCountry(?Model\CountryInterface $country): Model\AddressInterface
    {
        $this->country = $country;

        return $this;
    }

    public function getState(): ?Model\StateInterface
    {
        return $this->state;
    }

    public function setState(?Model\StateInterface $state): Model\AddressInterface
    {
        $this->state = $state;

        return $this;
    }

    public function getDigicode1(): ?string
    {
        return $this->digicode1;
    }

    public function setDigicode1(?string $digicode1): Model\AddressInterface
    {
        $this->digicode1 = $digicode1;

        return $this;
    }

    public function getDigicode2(): ?string
    {
        return $this->digicode2;
    }

    public function setDigicode2(?string $digicode2): Model\AddressInterface
    {
        $this->digicode2 = $digicode2;

        return $this;
    }

    public function getIntercom(): ?string
    {
        return $this->intercom;
    }

    public function setIntercom(?string $intercom): Model\AddressInterface
    {
        $this->intercom = $intercom;

        return $this;
    }

    public function getInformation(): ?string
    {
        return $this->information;
    }

    public function setInformation(?string $information): Model\AddressInterface
    {
        $this->information = $information;

        return $this;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setPhone(?PhoneNumber $phone): Model\AddressInterface
    {
        $this->phone = $phone;

        return $this;
    }

    public function getMobile(): ?PhoneNumber
    {
        return $this->mobile;
    }

    public function setMobile(?PhoneNumber $mobile): Model\AddressInterface
    {
        $this->mobile = $mobile;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): Model\AddressInterface
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): Model\AddressInterface
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function isEmpty(): bool
    {
        return empty($this->street) && empty($this->postalCode) && empty($this->city) && is_null($this->country);
    }
}
