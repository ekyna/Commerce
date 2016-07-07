<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxRuleInterface;

/**
 * Class TaxGroup
 * @package Ekyna\Component\Commerce\Pricing\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TaxGroup implements TaxGroupInterface
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
     * @var boolean
     */
    protected $default;

    /**
     * @var ArrayCollection|TaxRuleInterface[]
     */
    protected $taxRules;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->taxRules = new ArrayCollection();
        $this->default = false;
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
     * Returns whether this is the default tax group or not.
     *
     * @return boolean
     */
    public function isDefault()
    {
        return $this->default;
    }

    /**
     * Sets whether this is the default tax group or not.
     *
     * @param boolean $default
     *
     * @return TaxGroup
     */
    public function setDefault($default)
    {
        $this->default = (bool)$default;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxRules()
    {
        return 0 < $this->taxRules->count();
    }

    /**
     * @inheritdoc
     */
    public function getTaxRules()
    {
        return $this->taxRules;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxRule(TaxRuleInterface $taxRule)
    {
        return $this->taxRules->contains($taxRule);
    }

    /**
     * @inheritdoc
     */
    public function addTaxRule(TaxRuleInterface $taxRule)
    {
        if (!$this->hasTaxRule($taxRule)) {
            $this->taxRules->add($taxRule);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTaxRule(TaxRuleInterface $taxRule)
    {
        if ($this->hasTaxRule($taxRule)) {
            $this->taxRules->removeElement($taxRule);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxRules(ArrayCollection $taxRules)
    {
        $this->taxRules = $taxRules;
        return $this;
    }
}
