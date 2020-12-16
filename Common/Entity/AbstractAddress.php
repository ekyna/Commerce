<?php

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

    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $street;

    /**
     * @var string
     */
    protected $complement;

    /**
     * @var string
     */
    protected $supplement;

    /**
     * @var string
     */
    protected $extra;

    /**
     * @var string
     */
    protected $postalCode;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var Model\CountryInterface
     */
    protected $country;

    /**
     * @var Model\StateInterface
     */
    protected $state;

    /**
     * @var PhoneNumber
     */
    protected $phone;

    /**
     * @var PhoneNumber
     */
    protected $mobile;

    /**
     * @var string
     */
    protected $digicode1;

    /**
     * @var string
     */
    protected $digicode2;

    /**
     * @var string
     */
    protected $intercom;

    /**
     * @var string
     */
    protected $longitude;

    /**
     * @var string
     */
    protected $latitude;


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        if (empty($this->street) && empty($this->postalCode) && empty($this->city)) {
            return 'New address';
        }

        return trim(sprintf('%s %s %s', $this->street, $this->postalCode, $this->city));
    }

    /**
     * @inheritDoc
     */
    public function getCompany(): ?string
    {
        return $this->company;
    }

    /**
     * @inheritDoc
     */
    public function setCompany(string $company = null): Model\AddressInterface
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @inheritDoc
     */
    public function setStreet(string $street = null): Model\AddressInterface
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getComplement(): ?string
    {
        return $this->complement;
    }

    /**
     * @inheritDoc
     */
    public function setComplement(string $complement = null): Model\AddressInterface
    {
        $this->complement = $complement;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSupplement(): ?string
    {
        return $this->supplement;
    }

    /**
     * @inheritDoc
     */
    public function setSupplement(string $supplement = null): Model\AddressInterface
    {
        $this->supplement = $supplement;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExtra(): ?string
    {
        return $this->extra;
    }

    /**
     * @inheritDoc
     */
    public function setExtra(string $extra = null): Model\AddressInterface
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * @inheritDoc
     */
    public function setPostalCode(string $postalCode = null): Model\AddressInterface
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @inheritDoc
     */
    public function setCity(string $city = null): Model\AddressInterface
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCountry(): ?Model\CountryInterface
    {
        return $this->country;
    }

    /**
     * @inheritDoc
     */
    public function setCountry(Model\CountryInterface $country = null): Model\AddressInterface
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getState(): ?Model\StateInterface
    {
        return $this->state;
    }

    /**
     * @inheritDoc
     */
    public function setState(Model\StateInterface $state = null): Model\AddressInterface
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDigicode1(): ?string
    {
        return $this->digicode1;
    }

    /**
     * @inheritDoc
     */
    public function setDigicode1(string $digicode1 = null): Model\AddressInterface
    {
        $this->digicode1 = $digicode1;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDigicode2(): ?string
    {
        return $this->digicode2;
    }

    /**
     * @inheritDoc
     */
    public function setDigicode2(string $digicode2 = null): Model\AddressInterface
    {
        $this->digicode2 = $digicode2;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIntercom(): ?string
    {
        return $this->intercom;
    }

    /**
     * @inheritDoc
     */
    public function setIntercom(string $intercom = null): Model\AddressInterface
    {
        $this->intercom = $intercom;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    /**
     * @inheritDoc
     */
    public function setPhone(PhoneNumber $phone = null)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMobile(): ?PhoneNumber
    {
        return $this->mobile;
    }

    /**
     * @inheritDoc
     */
    public function setMobile(PhoneNumber $mobile = null)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    /**
     * @inheritDoc
     */
    public function setLongitude(float $longitude = null): Model\AddressInterface
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    /**
     * @inheritDoc
     */
    public function setLatitude(float $latitude = null): Model\AddressInterface
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty(): bool
    {
        return empty($this->street) && empty($this->postalCode) && empty($this->city) && is_null($this->country);
    }
}
