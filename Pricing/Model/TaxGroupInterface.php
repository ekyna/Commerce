<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Interface TaxGroupInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxGroupInterface
{
    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId();

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     * @return $this|TaxGroupInterface
     */
    public function setName($name);

    /**
     * Returns whether this is the default tax group or not.
     *
     * @return boolean
     */
    public function isDefault();

    /**
     * Sets whether this is the default tax group or not.
     *
     * @param boolean $default
     *
     * @return $this|TaxGroupInterface
     */
    public function setDefault($default);

    /**
     * Returns whether the tax group has tax rules or not.
     *
     * @return bool
     */
    public function hasTaxRules();

    /**
     * Returns the taxRules.
     *
     * @return ArrayCollection|TaxRuleInterface[]
     */
    public function getTaxRules();

    /**
     * Returns whether the tax group has the tax rule or not.
     *
     * @param TaxRuleInterface $taxRule
     *
     * @return bool
     */
    public function hasTaxRule(TaxRuleInterface $taxRule);

    /**
     * Adds the tax rule.
     *
     * @param TaxRuleInterface $taxRule
     *
     * @return $this|TaxGroupInterface
     */
    public function addTaxRule(TaxRuleInterface $taxRule);

    /**
     * Removes the tax rule.
     *
     * @param TaxRuleInterface $taxRule
     *
     * @return $this|TaxGroupInterface
     */
    public function removeTaxRule(TaxRuleInterface $taxRule);

    /**
     * Sets the taxRules.
     *
     * @param ArrayCollection|TaxRuleInterface[] $taxRules
     * @return $this|TaxGroupInterface
     */
    public function setTaxRules(ArrayCollection $taxRules);
}
