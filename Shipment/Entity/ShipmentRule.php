<?php

namespace Ekyna\Component\Commerce\Shipment\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\VatDisplayModes;
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
     * @var DateTime
     */
    protected $startAt;

    /**
     * @var DateTime
     */
    protected $endAt;

    /**
     * @var float
     */
    protected $netPrice;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->methods = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();

        $this->baseTotal = 0.;
        $this->vatMode = VatDisplayModes::MODE_NET;
        $this->netPrice = 0.;
    }

    /**
     * Returns the string representation.
     *
     * @return string
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
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName(string $name): ShipmentRuleInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getMethods(): Collection
    {
        return $this->methods;
    }

    /**
     * @inheritdoc
     */
    public function addMethod(ShipmentMethodInterface $method): ShipmentRuleInterface
    {
        if (!$this->methods->contains($method)) {
            $this->methods->add($method);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeMethod(ShipmentMethodInterface $method): ShipmentRuleInterface
    {
        if ($this->methods->contains($method)) {
            $this->methods->removeElement($method);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    /**
     * @inheritdoc
     */
    public function addCountry(CountryInterface $country): ShipmentRuleInterface
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCountry(CountryInterface $country): ShipmentRuleInterface
    {
        if ($this->countries->contains($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerGroups(): Collection
    {
        return $this->customerGroups;
    }

    /**
     * @inheritdoc
     */
    public function addCustomerGroup(CustomerGroupInterface $group): ShipmentRuleInterface
    {
        if (!$this->customerGroups->contains($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCustomerGroup(CustomerGroupInterface $group): ShipmentRuleInterface
    {
        if ($this->customerGroups->contains($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBaseTotal(): float
    {
        return $this->baseTotal;
    }

    /**
     * @inheritdoc
     */
    public function setBaseTotal(float $total): ShipmentRuleInterface
    {
        $this->baseTotal = $total;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVatMode(): string
    {
        return $this->vatMode;
    }

    /**
     * @inheritdoc
     */
    public function setVatMode(string $mode): ShipmentRuleInterface
    {
        $this->vatMode = $mode;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getStartAt(): ?DateTime
    {
        return $this->startAt;
    }

    /**
     * @inheritdoc
     */
    public function setStartAt(DateTime $date = null): ShipmentRuleInterface
    {
        $this->startAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getEndAt(): ?DateTime
    {
        return $this->endAt;
    }

    /**
     * @inheritdoc
     */
    public function setEndAt(DateTime $date = null): ShipmentRuleInterface
    {
        $this->endAt = $date;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice(): float
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice(float $price): ShipmentRuleInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
