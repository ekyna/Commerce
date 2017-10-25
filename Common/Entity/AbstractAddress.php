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
}
