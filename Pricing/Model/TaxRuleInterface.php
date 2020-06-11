<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\CountryInterface;
use Ekyna\Component\Commerce\Common\Model\MentionSubjectInterface;
use Ekyna\Component\Commerce\Pricing\Entity\TaxRuleMention;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface TaxRuleInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxRuleInterface extends RM\ResourceInterface, MentionSubjectInterface
{
    /**
     * Returns the code.
     *
     * @return string
     */
    public function getCode(): ?string;

    /**
     * Sets the code.
     *
     * @param string $code
     *
     * @return $this|TaxRuleInterface
     */
    public function setCode(string $code): TaxRuleInterface;

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|TaxRuleInterface
     */
    public function setName(string $name): TaxRuleInterface;

    /**
     * Returns the customer.
     *
     * @return bool
     */
    public function isCustomer(): bool;

    /**
     * Sets the customer.
     *
     * @param bool $customer
     *
     * @return $this|TaxRuleInterface
     */
    public function setCustomer(bool $customer): TaxRuleInterface;

    /**
     * Returns the business.
     *
     * @return bool
     */
    public function isBusiness(): bool;

    /**
     * Sets the business.
     *
     * @param bool $business
     *
     * @return $this|TaxRuleInterface
     */
    public function setBusiness(bool $business): TaxRuleInterface;

    /**
     * Returns whether the tax rule has source countries.
     *
     * @return bool
     */
    public function hasSources(): bool;

    /**
     * Returns the source countries.
     *
     * @return Collection|CountryInterface[]
     */
    public function getSources(): Collection;

    /**
     * Returns whether the tax rule has the given source country.
     *
     * @param CountryInterface $source
     *
     * @return bool
     */
    public function hasSource(CountryInterface $source): bool;

    /**
     * Adds the source country.
     *
     * @param CountryInterface $source
     *
     * @return $this|TaxRuleInterface
     */
    public function addSource(CountryInterface $source): TaxRuleInterface;

    /**
     * Removes the source country.
     *
     * @param CountryInterface $source
     *
     * @return $this|TaxRuleInterface
     */
    public function removeSource(CountryInterface $source): TaxRuleInterface;

    /**
     * Sets the countries.
     *
     * @param CountryInterface[] $countries
     *
     * @return $this|TaxRuleInterface
     */
    public function setSources(array $countries): TaxRuleInterface;

    /**
     * Returns whether the tax rule has target countries.
     *
     * @return bool
     */
    public function hasTargets(): bool;

    /**
     * Returns the target countries.
     *
     * @return Collection|CountryInterface[]
     */
    public function getTargets(): Collection;

    /**
     * Returns whether the tax rule has the given target country.
     *
     * @param CountryInterface $target
     *
     * @return bool
     */
    public function hasTarget(CountryInterface $target): bool;

    /**
     * Adds the target country.
     *
     * @param CountryInterface $target
     *
     * @return $this|TaxRuleInterface
     */
    public function addTarget(CountryInterface $target): TaxRuleInterface;

    /**
     * Removes the target country.
     *
     * @param CountryInterface $target
     *
     * @return $this|TaxRuleInterface
     */
    public function removeTarget(CountryInterface $target): TaxRuleInterface;

    /**
     * Sets the countries.
     *
     * @param CountryInterface[] $countries
     *
     * @return $this|TaxRuleInterface
     */
    public function setTargets(array $countries): TaxRuleInterface;

    /**
     * Returns whether the tax rule has taxes.
     *
     * @return bool
     */
    public function hasTaxes(): bool;

    /**
     * Returns the taxes.
     *
     * @return Collection|TaxInterface[]
     */
    public function getTaxes(): Collection;

    /**
     * Returns whether the tax rule has the given tax.
     *
     * @param TaxInterface $tax
     *
     * @return bool
     */
    public function hasTax(TaxInterface $tax): bool;

    /**
     * Adds the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|TaxRuleInterface
     */
    public function addTax(TaxInterface $tax): TaxRuleInterface;

    /**
     * Removes the tax.
     *
     * @param TaxInterface $tax
     *
     * @return $this|TaxRuleInterface
     */
    public function removeTax(TaxInterface $tax): TaxRuleInterface;

    /**
     * Sets the taxes.
     *
     * @param TaxInterface[] $taxes
     *
     * @return $this|TaxRuleInterface
     */
    public function setTaxes(array $taxes): TaxRuleInterface;

    /**
     * Returns whether this tax rule has the given mention.
     *
     * @param TaxRuleMention $mention
     *
     * @return bool
     */
    public function hasMention(TaxRuleMention $mention): bool;

    /**
     * Adds the mention.
     *
     * @param TaxRuleMention $mention
     *
     * @return $this|TaxRuleInterface
     */
    public function addMention(TaxRuleMention $mention): TaxRuleInterface;

    /**
     * Removes the mention.
     *
     * @param TaxRuleMention $mention
     *
     * @return $this|TaxRuleInterface
     */
    public function removeMention(TaxRuleMention $mention): TaxRuleInterface;

    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Sets the priority.
     *
     * @param int $priority
     *
     * @return $this|TaxRuleInterface
     */
    public function setPriority(int $priority): TaxRuleInterface;
}
