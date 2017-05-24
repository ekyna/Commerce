<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;

/**
 * Class TaxRule
 * @package Ekyna\Component\Commerce\Pricing\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRule implements TaxRuleInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $customer;

    /**
     * @var bool
     */
    protected $business;

    /**
     * @var ArrayCollection|CountryInterface[]
     */
    protected $countries;

    /**
     * @var ArrayCollection|TaxInterface[]
     */
    protected $taxes;

    /**
     * @var array
     */
    protected $notices;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var int
     */
    protected $position;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->customer = false;
        $this->business = false;

        $this->countries = new ArrayCollection();
        $this->taxes = new ArrayCollection();
        $this->notices = [];

        $this->priority = 0;
        $this->position = 0;
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the customer.
     *
     * @return bool
     */
    public function isCustomer()
    {
        return $this->customer;
    }

    /**
     * Sets the customer.
     *
     * @param bool $customer
     *
     * @return TaxRule
     */
    public function setCustomer($customer)
    {
        $this->customer = (bool)$customer;

        return $this;
    }

    /**
     * Returns the business.
     *
     * @return bool
     */
    public function isBusiness()
    {
        return $this->business;
    }

    /**
     * Sets the business.
     *
     * @param bool $business
     *
     * @return TaxRule
     */
    public function setBusiness($business)
    {
        $this->business = (bool)$business;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasCountries()
    {
        return 0 < $this->countries->count();
    }

    /**
     * @inheritdoc
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * @inheritdoc
     */
    public function hasCountry(CountryInterface $country)
    {
        return $this->countries->contains($country);
    }

    /**
     * @inheritdoc
     */
    public function addCountry(CountryInterface $country)
    {
        if (!$this->hasCountry($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCountry(CountryInterface $country)
    {
        if ($this->hasCountry($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCountries(array $countries)
    {
        foreach ($this->countries as $country) {
            $this->removeCountry($country);
        }

        foreach ($countries as $country) {
            $this->addCountry($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxes()
    {
        return 0 < $this->taxes->count();
    }

    /**
     * @inheritdoc
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @inheritdoc
     */
    public function hasTax(TaxInterface $tax)
    {
        return $this->taxes->contains($tax);
    }

    /**
     * @inheritdoc
     */
    public function addTax(TaxInterface $tax)
    {
        if (!$this->hasTax($tax)) {
            $this->taxes->add($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTax(TaxInterface $tax)
    {
        if ($this->hasTax($tax)) {
            $this->taxes->removeElement($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxes(array $taxes)
    {
        foreach ($this->taxes as $tax) {
            $this->removeTax($tax);
        }

        foreach ($taxes as $tax) {
            $this->addTax($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNotices()
    {
        return $this->notices;
    }

    /**
     * @inheritdoc
     */
    public function setNotices(array $notices)
    {
        $this->notices = $notices;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }
}
