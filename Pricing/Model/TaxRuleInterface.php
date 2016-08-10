<?php

namespace Ekyna\Component\Commerce\Pricing\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;

/**
 * Interface TaxRuleInterface
 * @package Ekyna\Component\Commerce\Pricing\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TaxRuleInterface extends ResourceInterface
{
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
     * @return $this|TaxRuleInterface
     */
    public function setName($name);

    /**
     * Returns whether the tax rule has customer groups or not.
     *
     * @return bool
     */
    public function hasCustomerGroups();

    /**
     * Returns the customerGroups.
     *
     * @return ArrayCollection|CustomerGroupInterface[]
     */
    public function getCustomerGroups();

    /**
     * Returns whether the tax rule has the customer group or not.
     *
     * @param CustomerGroupInterface $customerGroup
     * @return bool
     */
    public function hasCustomerGroup(CustomerGroupInterface $customerGroup);

    /**
     * Adds the customer group.
     *
     * @param CustomerGroupInterface $customerGroup
     * @return $this|TaxRuleInterface
     */
    public function addCustomerGroup(CustomerGroupInterface $customerGroup);

    /**
     * Removes the customer group.
     *
     * @param CustomerGroupInterface $customerGroup
     * @return $this|TaxRuleInterface
     */
    public function removeCustomerGroup(CustomerGroupInterface $customerGroup);

    /**
     * Sets the customerGroups.
     *
     * @param ArrayCollection|CustomerGroupInterface[] $customerGroups
     * @return $this|TaxRuleInterface
     */
    public function setCustomerGroups(ArrayCollection $customerGroups);

    /**
     * Returns whether the tax rule has tax groups.
     *
     * @return bool
     */
    public function hasTaxGroups();

    /**
     * Returns the taxGroups.
     *
     * @return ArrayCollection|TaxGroupInterface[]
     */
    public function getTaxGroups();

    /**
     * Returns whether the tax rule has the tax group or not.
     *
     * @param TaxGroupInterface $taxGroup
     * @return bool
     */
    public function hasTaxGroup(TaxGroupInterface $taxGroup);

    /**
     * Adds the tax group.
     *
     * @param TaxGroupInterface $taxGroup
     * @return $this|TaxRuleInterface
     */
    public function addTaxGroup(TaxGroupInterface $taxGroup);

    /**
     * Removes the tax group.
     *
     * @param TaxGroupInterface $taxGroup
     * @return $this|TaxRuleInterface
     */
    public function removeTaxGroup(TaxGroupInterface $taxGroup);

    /**
     * Sets the taxGroups.
     *
     * @param ArrayCollection|TaxGroupInterface[] $taxGroups
     * @return $this|TaxRuleInterface
     */
    public function setTaxGroups(ArrayCollection $taxGroups);

    /**
     * Returns whether the tax rule has taxes or not.
     *
     * @return bool
     */
    public function hasTaxes();

    /**
     * Returns the taxes.
     *
     * @return ArrayCollection|TaxInterface[]
     */
    public function getTaxes();

    /**
     * Returns whether the tax rule has the tax or not.
     *
     * @param TaxInterface $tax
     * @return bool
     */
    public function hasTax(TaxInterface $tax);

    /**
     * Adds the tax.
     *
     * @param TaxInterface $tax
     * @return $this|TaxRuleInterface
     */
    public function addTax(TaxInterface $tax);

    /**
     * Removes the tax.
     *
     * @param TaxInterface $tax
     * @return $this|TaxRuleInterface
     */
    public function removeTax(TaxInterface $tax);

    /**
     * Sets the taxes.
     *
     * @param ArrayCollection|TaxInterface[] $taxes
     * @return $this|TaxRuleInterface
     */
    public function setTaxes(ArrayCollection $taxes);

    /**
     * Returns the priority.
     *
     * @return int
     */
    public function getPriority();

    /**
     * Sets the priority.
     *
     * @param int $priority
     * @return $this|TaxRuleInterface
     */
    public function setPriority($priority);

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition();

    /**
     * Sets the position.
     *
     * @param int $position
     * @return $this|TaxRuleInterface
     */
    public function setPosition($position);
}
