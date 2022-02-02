<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\MentionSubjectTrait;
use Ekyna\Component\Commerce\Pricing\Model\TaxInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;
use Ekyna\Component\Resource\Model\AbstractResource;

/**
 * Class TaxRule
 * @package Ekyna\Component\Commerce\Pricing\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxRule extends AbstractResource implements TaxRuleInterface
{
    use MentionSubjectTrait;

    protected ?string $code     = null;
    protected ?string $name     = null;
    protected bool    $customer = false;
    protected bool    $business = false;
    /** @var Collection|CountryInterface[] */
    protected Collection $sources;
    /** @var Collection|CountryInterface[] */
    protected Collection $targets;
    /** @var Collection|TaxInterface[] */
    protected Collection $taxes;
    protected int        $priority = 0;


    public function __construct()
    {
        $this->sources = new ArrayCollection();
        $this->targets = new ArrayCollection();
        $this->taxes = new ArrayCollection();

        $this->initializeMentions();
    }

    /**
     * Returns the string representation.
     */
    public function __toString(): string
    {
        return $this->name ?: 'New tax rule';
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): TaxRuleInterface
    {
        $this->code = $code;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): TaxRuleInterface
    {
        $this->name = $name;

        return $this;
    }

    public function isCustomer(): bool
    {
        return $this->customer;
    }

    public function setCustomer(bool $customer): TaxRuleInterface
    {
        $this->customer = $customer;

        return $this;
    }

    public function isBusiness(): bool
    {
        return $this->business;
    }

    public function setBusiness(bool $business): TaxRuleInterface
    {
        $this->business = $business;

        return $this;
    }

    public function hasSources(): bool
    {
        return 0 < $this->sources->count();
    }

    public function getSources(): Collection
    {
        return $this->sources;
    }

    public function hasSource(CountryInterface $source): bool
    {
        return $this->sources->contains($source);
    }

    public function addSource(CountryInterface $source): TaxRuleInterface
    {
        if (!$this->hasSource($source)) {
            $this->sources->add($source);
        }

        return $this;
    }

    public function removeSource(CountryInterface $source): TaxRuleInterface
    {
        if ($this->hasSource($source)) {
            $this->sources->removeElement($source);
        }

        return $this;
    }

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

    public function hasTargets(): bool
    {
        return 0 < $this->targets->count();
    }

    public function getTargets(): Collection
    {
        return $this->targets;
    }

    public function hasTarget(CountryInterface $target): bool
    {
        return $this->targets->contains($target);
    }

    public function addTarget(CountryInterface $target): TaxRuleInterface
    {
        if (!$this->hasTarget($target)) {
            $this->targets->add($target);
        }

        return $this;
    }

    public function removeTarget(CountryInterface $target): TaxRuleInterface
    {
        if ($this->hasTarget($target)) {
            $this->targets->removeElement($target);
        }

        return $this;
    }

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

    public function hasTaxes(): bool
    {
        return 0 < $this->taxes->count();
    }

    public function getTaxes(): Collection
    {
        return $this->taxes;
    }

    public function hasTax(TaxInterface $tax): bool
    {
        return $this->taxes->contains($tax);
    }

    public function addTax(TaxInterface $tax): TaxRuleInterface
    {
        if (!$this->hasTax($tax)) {
            $this->taxes->add($tax);
        }

        return $this;
    }

    public function removeTax(TaxInterface $tax): TaxRuleInterface
    {
        if ($this->hasTax($tax)) {
            $this->taxes->removeElement($tax);
        }

        return $this;
    }

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

    public function hasMention(TaxRuleMention $mention): bool
    {
        return $this->mentions->contains($mention);
    }

    public function addMention(TaxRuleMention $mention): TaxRuleInterface
    {
        if (!$this->hasMention($mention)) {
            $this->mentions->add($mention);
            $mention->setTaxRule($this);
        }

        return $this;
    }

    public function removeMention(TaxRuleMention $mention): TaxRuleInterface
    {
        if ($this->hasMention($mention)) {
            $this->mentions->removeElement($mention);
            $mention->setTaxRule(null);
        }

        return $this;
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): TaxRuleInterface
    {
        $this->priority = $priority;

        return $this;
    }
}
