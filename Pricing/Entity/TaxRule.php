<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    protected $code;

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
    protected $sources;

    /**
     * @var ArrayCollection|CountryInterface[]
     */
    protected $targets;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->customer = false;
        $this->business = false;
        $this->sources = new ArrayCollection();
        $this->targets = new ArrayCollection();
        $this->taxes = new ArrayCollection();
        $this->notices = [];
        $this->priority = 0;
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
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function setCode(string $code): TaxRuleInterface
    {
        $this->code = $code;

        return $this;
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
    public function setName(string $name): TaxRuleInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the customer.
     *
     * @return bool
     */
    public function isCustomer(): bool
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
    public function setCustomer(bool $customer): TaxRuleInterface
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Returns the business.
     *
     * @return bool
     */
    public function isBusiness(): bool
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
    public function setBusiness(bool $business): TaxRuleInterface
    {
        $this->business = $business;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasSources(): bool
    {
        return 0 < $this->sources->count();
    }

    /**
     * @inheritdoc
     */
    public function getSources(): Collection
    {
        return $this->sources;
    }

    /**
     * @inheritdoc
     */
    public function hasSource(CountryInterface $source): bool
    {
        return $this->sources->contains($source);
    }

    /**
     * @inheritdoc
     */
    public function addSource(CountryInterface $source): TaxRuleInterface
    {
        if (!$this->hasSource($source)) {
            $this->sources->add($source);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeSource(CountryInterface $source): TaxRuleInterface
    {
        if ($this->hasSource($source)) {
            $this->sources->removeElement($source);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setSources(array $sources): TaxRuleInterface
    {
        foreach ($this->sources as $source) {
            $this->removeSource($source);
        }

        foreach ($sources as $source) {
            $this->addSource($source);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasTargets(): bool
    {
        return 0 < $this->targets->count();
    }

    /**
     * @inheritdoc
     */
    public function getTargets(): Collection
    {
        return $this->targets;
    }

    /**
     * @inheritdoc
     */
    public function hasTarget(CountryInterface $target): bool
    {
        return $this->targets->contains($target);
    }

    /**
     * @inheritdoc
     */
    public function addTarget(CountryInterface $target): TaxRuleInterface
    {
        if (!$this->hasTarget($target)) {
            $this->targets->add($target);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTarget(CountryInterface $target): TaxRuleInterface
    {
        if ($this->hasTarget($target)) {
            $this->targets->removeElement($target);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTargets(array $targets): TaxRuleInterface
    {
        foreach ($this->targets as $target) {
            $this->removeTarget($target);
        }

        foreach ($targets as $target) {
            $this->addTarget($target);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxes(): bool
    {
        return 0 < $this->taxes->count();
    }

    /**
     * @inheritdoc
     */
    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    /**
     * @inheritdoc
     */
    public function hasTax(TaxInterface $tax): bool
    {
        return $this->taxes->contains($tax);
    }

    /**
     * @inheritdoc
     */
    public function addTax(TaxInterface $tax): TaxRuleInterface
    {
        if (!$this->hasTax($tax)) {
            $this->taxes->add($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTax(TaxInterface $tax): TaxRuleInterface
    {
        if ($this->hasTax($tax)) {
            $this->taxes->removeElement($tax);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxes(array $taxes): TaxRuleInterface
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
    public function getNotices(): array
    {
        return $this->notices;
    }

    /**
     * @inheritdoc
     */
    public function setNotices(array $notices): TaxRuleInterface
    {
        $this->notices = $notices;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority(int $priority): TaxRuleInterface
    {
        $this->priority = $priority;

        return $this;
    }
}
