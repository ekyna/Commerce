<?php

namespace Ekyna\Component\Commerce\Address\Entity;

use Ekyna\Component\Commerce\Address\Model\AddressInterface;
use Ekyna\Component\Commerce\Address\Model\CountryInterface;
use Ekyna\Component\Commerce\Address\Model\StateInterface;

/**
 * Class AbstractAddress
 * @package Ekyna\Component\Commerce\Address\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAddress implements AddressInterface
{
    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $street;

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
     * @var CountryInterface
     */
    protected $country;

    /**
     * @var StateInterface
     */
    protected $state;


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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @inheritdoc
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @inheritdoc
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
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
    public function setCountry(CountryInterface $country)
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
    public function setState(StateInterface $state = null)
    {
        $this->state = $state;
        return $this;
    }
}
