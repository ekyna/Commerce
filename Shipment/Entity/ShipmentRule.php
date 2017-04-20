<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Shipment\Entity;

use DateTimeInterface;
use Decimal\Decimal;
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
    protected ?int               $id      = null;
    protected ?string            $name    = null;
    protected Decimal            $baseTotal;
    protected string             $vatMode;
    protected ?DateTimeInterface $startAt = null;
    protected ?DateTimeInterface $endAt   = null;
    protected Decimal            $netPrice;
    /** @var Collection<ShipmentMethodInterface> */
    protected Collection $methods;
    /** @var Collection<CountryInterface> */
    protected Collection $countries;
    /** @var Collection<CustomerGroupInterface> */
    protected Collection $customerGroups;

    public function __construct()
    {
        $this->baseTotal = new Decimal(0);
        $this->vatMode = VatDisplayModes::MODE_NET;
        $this->netPrice = new Decimal(0);

        $this->methods = new ArrayCollection();
        $this->countries = new ArrayCollection();
        $this->customerGroups = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->name ?: 'New shipment rule';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): ShipmentRuleInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getMethods(): Collection
    {
        return $this->methods;
    }

    public function addMethod(ShipmentMethodInterface $method): ShipmentRuleInterface
    {
        if (!$this->methods->contains($method)) {
            $this->methods->add($method);
        }

        return $this;
    }

    public function removeMethod(ShipmentMethodInterface $method): ShipmentRuleInterface
    {
        if ($this->methods->contains($method)) {
            $this->methods->removeElement($method);
        }

        return $this;
    }

    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function addCountry(CountryInterface $country): ShipmentRuleInterface
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
        }

        return $this;
    }

    public function removeCountry(CountryInterface $country): ShipmentRuleInterface
    {
        if ($this->countries->contains($country)) {
            $this->countries->removeElement($country);
        }

        return $this;
    }

    public function getCustomerGroups(): Collection
    {
        return $this->customerGroups;
    }

    public function addCustomerGroup(CustomerGroupInterface $group): ShipmentRuleInterface
    {
        if (!$this->customerGroups->contains($group)) {
            $this->customerGroups->add($group);
        }

        return $this;
    }

    public function removeCustomerGroup(CustomerGroupInterface $group): ShipmentRuleInterface
    {
        if ($this->customerGroups->contains($group)) {
            $this->customerGroups->removeElement($group);
        }

        return $this;
    }

    public function getBaseTotal(): Decimal
    {
        return $this->baseTotal;
    }

    public function setBaseTotal(Decimal $total): ShipmentRuleInterface
    {
        $this->baseTotal = $total;

        return $this;
    }

    public function getVatMode(): string
    {
        return $this->vatMode;
    }

    public function setVatMode(string $mode): ShipmentRuleInterface
    {
        $this->vatMode = $mode;

        return $this;
    }

    public function getStartAt(): ?DateTimeInterface
    {
        return $this->startAt;
    }

    public function setStartAt(?DateTimeInterface $date): ShipmentRuleInterface
    {
        $this->startAt = $date;

        return $this;
    }

    public function getEndAt(): ?DateTimeInterface
    {
        return $this->endAt;
    }

    public function setEndAt(?DateTimeInterface $date): ShipmentRuleInterface
    {
        $this->endAt = $date;

        return $this;
    }

    public function getNetPrice(): Decimal
    {
        return $this->netPrice;
    }

    public function setNetPrice(Decimal $price): ShipmentRuleInterface
    {
        $this->netPrice = $price;

        return $this;
    }
}
