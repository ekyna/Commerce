<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentMethodInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentRuleInterface;

/**
 * Class ShipmentRule
 * @package Ekyna\Component\Commerce\Shipment\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentRule implements ShipmentRuleInterface
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
     * @var ArrayCollection|ShipmentMethodInterface[]
     */
    protected $methods;

    /**
     * @var ArrayCollection|CountryInterface[]
     */
    protected $countries;

    /**
     * @var ArrayCollection|CustomerGroupInterface[]
     */
    protected $customerGroups;

    /**
     * @var float
     */
    protected $baseTotal;

    /**
     * @var string
     */
    protected $vatMode;

    /**
     * @var \DateTime
     */
    protected $startAt;

    /**
     * @var \DateTime
     */
    protected $endAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->methods = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->name;
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
     * @inheritdoc
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @inheritdoc
     */
    public function addMethod(ShipmentMethodInterface $method)
    {
        if (!$this->methods->contains($method)) {
            $this->methods->add($method);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeMethod(ShipmentMethodInterface $method)
    {
        if ($this->methods->contains($method)) {
            $this->methods->removeElement($method);
        }

        return $this;
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
    public function addCountry(CountryInterface $country)
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCountry(CountryInterface $country)
    {
        if ($this->countries->contains($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }    

    /**
     * @inheritdoc
     */
    public function getCustomerGroups()
    {
        return $this->customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function addCustomerGroup(CustomerGroupInterface $group)
    {
        if (!$this->customerGroups->contains($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCustomerGroup(CustomerGroupInterface $group)
    {
        if ($this->customerGroups->contains($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseTotal()
    {
        return $this->baseTotal;
    }

    /**
     * @inheritdoc
     */
    public function setBaseTotal($total)
    {
        $this->baseTotal = (float)$total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVatMode()
    {
        return $this->vatMode;
    }

    /**
     * @inheritdoc
     */
    public function setVatMode($vatMode)
    {
        $this->vatMode = $vatMode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStartAt()
    {
        return $this->startAt;
    }

    /**
     * @inheritdoc
     */
    public function setStartAt(\DateTime $date = null)
    {
        $this->startAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEndAt()
    {
        return $this->endAt;
    }

    /**
     * @inheritdoc
     */
    public function setEndAt(\DateTime $date = null)
    {
        $this->endAt = $date;

        return $this;
    }
}
