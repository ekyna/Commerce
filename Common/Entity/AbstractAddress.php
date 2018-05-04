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
     * @inheritdoc
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @inheritdoc
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @inheritdoc
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getComplement()
    {
        return $this->complement;
    }

    /**
     * @inheritdoc
     */
    public function setComplement($complement)
    {
        $this->complement = $complement;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSupplement()
    {
        return $this->supplement;
    }

    /**
     * @inheritdoc
     */
    public function setSupplement($supplement)
    {
        $this->supplement = $supplement;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * @inheritdoc
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * @inheritdoc
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @inheritdoc
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @inheritdoc
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @inheritdoc
     */
    public function setCountry(Model\CountryInterface $country = null)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @inheritdoc
     */
    public function setState(Model\StateInterface $state = null)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDigicode1()
    {
        return $this->digicode1;
    }

    /**
     * @inheritdoc
     */
    public function setDigicode1($digicode1)
    {
        $this->digicode1 = $digicode1;
    }

    /**
     * @inheritdoc
     */
    public function getDigicode2()
    {
        return $this->digicode2;
    }

    /**
     * @inheritdoc
     */
    public function setDigicode2($digicode2)
    {
        $this->digicode2 = $digicode2;
    }

    /**
     * @inheritdoc
     */
    public function getIntercom()
    {
        return $this->intercom;
    }

    /**
     * @inheritdoc
     */
    public function setIntercom($intercom)
    {
        $this->intercom = $intercom;
    }

    /**
     * @inheritdoc
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @inheritdoc
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @inheritdoc
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @inheritdoc
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @inheritdoc
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }
}
