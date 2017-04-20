<?php

declare(strict_types=1);

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
     */
    public function getCode(): ?string;

    /**
     * Sets the code.
     *
     * @return $this|TaxRuleInterface
     */
    public function setCode(string $code): TaxRuleInterface;

    /**
     * Returns the name.
     */
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @return $this|TaxRuleInterface
     */
    public function setName(string $name): TaxRuleInterface;

    /**
     * Returns whether the tax rule applies to regular customer groups.
     */
    public function isCustomer(): bool;

    /**
     * Sets whether the tax rule applies to regular customer groups.
     *
     * @return $this|TaxRuleInterface
     */
    public function setCustomer(bool $customer): TaxRuleInterface;

    /**
     * Returns whether the tax rule applies to business customer groups.
     */
    public function isBusiness(): bool;

    /**
     * Sets whether the tax rule applies to business customer groups.
     *
     * @return $this|TaxRuleInterface
     */
    public function setBusiness(bool $business): TaxRuleInterface;

    /**
     * Returns whether the tax rule has source countries.
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
     */
    public function hasSource(CountryInterface $source): bool;

    /**
     * Adds the source country.
     *
     * @return $this|TaxRuleInterface
     */
    public function addSource(CountryInterface $source): TaxRuleInterface;

    /**
     * Removes the source country.
     *
     * @return $this|TaxRuleInterface
     */
    public function removeSource(CountryInterface $source): TaxRuleInterface;

    /**
     * Sets the countries.
     *
     * @return $this|TaxRuleInterface
     */
    public function setSources(array $sources): TaxRuleInterface;

    /**
     * Returns whether the tax rule has target countries.
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
     */
    public function hasTarget(CountryInterface $target): bool;

    /**
     * Adds the target country.
     *
     * @return $this|TaxRuleInterface
     */
    public function addTarget(CountryInterface $target): TaxRuleInterface;

    /**
     * Removes the target country.
     *
     * @return $this|TaxRuleInterface
     */
    public function removeTarget(CountryInterface $target): TaxRuleInterface;

    /**
     * Sets the countries.
     *
     * @return $this|TaxRuleInterface
     */
    public function setTargets(array $targets): TaxRuleInterface;

    /**
     * Returns whether the tax rule has taxes.
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
     */
    public function hasTax(TaxInterface $tax): bool;

    /**
     * Adds the tax.
     *
     * @return $this|TaxRuleInterface
     */
    public function addTax(TaxInterface $tax): TaxRuleInterface;

    /**
     * Removes the tax.
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
     */
    public function hasMention(TaxRuleMention $mention): bool;

    /**
     * Adds the mention.
     *
     * @return $this|TaxRuleInterface
     */
    public function addMention(TaxRuleMention $mention): TaxRuleInterface;

    /**
     * Removes the mention.
     *
     * @return $this|TaxRuleInterface
     */
    public function removeMention(TaxRuleMention $mention): TaxRuleInterface;

    /**
     * Returns the priority.
     */
    public function getPriority(): int;

    /**
     * Sets the priority.
     *
     * @return $this|TaxRuleInterface
     */
    public function setPriority(int $priority): TaxRuleInterface;
}
