<?php

namespace Ekyna\Component\Commerce\Common\Entity;

use Ekyna\Component\Commerce\Common\Model;

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
     * @var string
     */
    protected $phone;

    /**
     * @var string
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
    public function __toString()
    {
        return sprintf('%s %s %s', $this->street, $this->postalCode, $this->city);
    }

    /**
     * @inheritDoc
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @inheritDoc
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @inheritDoc
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getComplement()
    {
        return $this->complement;
    }

    /**
     * @inheritDoc
     */
    public function setComplement($complement)
    {
        $this->complement = $complement;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSupplement()
    {
        return $this->supplement;
    }

    /**
     * @inheritDoc
     */
    public function setSupplement($supplement)
    {
        $this->supplement = $supplement;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @inheritDoc
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @inheritDoc
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @inheritDoc
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @inheritDoc
     */
    public function setCountry(Model\CountryInterface $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @inheritDoc
     */
    public function setState(Model\StateInterface $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDigicode1()
    {
        return $this->digicode1;
    }

    /**
     * @inheritDoc
     */
    public function setDigicode1($digicode1)
    {
        $this->digicode1 = $digicode1;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getDigicode2()
    {
        return $this->digicode2;
    }

    /**
     * @inheritDoc
     */
    public function setDigicode2($digicode2)
    {
        $this->digicode2 = $digicode2;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIntercom()
    {
        return $this->intercom;
    }

    /**
     * @inheritDoc
     */
    public function setIntercom($intercom)
    {
        $this->intercom = $intercom;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @inheritDoc
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @inheritDoc
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @inheritDoc
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @inheritDoc
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isEmpty()
    {
        return empty($this->street) && empty($this->postalCode) && empty($this->city) && is_null($this->country);
    }
}
