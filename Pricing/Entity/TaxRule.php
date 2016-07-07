<?php

namespace Ekyna\Component\Commerce\Pricing\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Customer\Model\CustomerGroupInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
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
    protected $name;

    /**
     * @var ArrayCollection|CustomerGroupInterface[]
     */
    protected $customerGroups;

    /**
     * @var ArrayCollection|TaxGroupInterface[]
     */
    protected $taxGroups;

    /**
     * @var ArrayCollection|TaxInterface[]
     */
    protected $taxes;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var int
     */
    protected $position;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->customerGroups = new ArrayCollection();
        $this->taxGroups = new ArrayCollection();
        $this->taxes = new ArrayCollection();

        $this->priority = 0;
        $this->position = 0;
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
     * @inheritdoc
     */
    public function hasCustomerGroups()
    {
        return 0 < $this->customerGroups->count();
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
    public function hasCustomerGroup(CustomerGroupInterface $customerGroup)
    {
        return $this->customerGroups->contains($customerGroup);
    }

    /**
     * @inheritdoc
     */
    public function addCustomerGroup(CustomerGroupInterface $customerGroup)
    {
        if (!$this->hasCustomerGroup($customerGroup)) {
            $this->customerGroups->add($customerGroup);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeCustomerGroup(CustomerGroupInterface $customerGroup)
    {
        if ($this->hasCustomerGroup($customerGroup)) {
            $this->customerGroups->removeElement($customerGroup);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerGroups(ArrayCollection $customerGroups)
    {
        $this->customerGroups = $customerGroups;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxGroups()
    {
        return 0 < $this->taxGroups->count();
    }

    /**
     * @inheritdoc
     */
    public function getTaxGroups()
    {
        return $this->taxGroups;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxGroup(TaxGroupInterface $taxGroup)
    {
        return $this->taxGroups->contains($taxGroup);
    }

    /**
     * @inheritdoc
     */
    public function addTaxGroup(TaxGroupInterface $taxGroup)
    {
        if (!$this->hasTaxGroup($taxGroup)) {
            $taxGroup->addTaxRule($this);
            $this->taxGroups->add($taxGroup);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTaxGroup(TaxGroupInterface $taxGroup)
    {
        if ($this->hasTaxGroup($taxGroup)) {
            $taxGroup->removeTaxRule($this);
            $this->taxGroups->removeElement($taxGroup);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxGroups(ArrayCollection $taxGroups)
    {
        $this->taxGroups = $taxGroups;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function hasTaxes()
    {
        return 0 < $this->taxes->count();
    }

    /**
     * @inheritdoc
     */
    public function getTaxes()
    {
        return $this->taxes;
    }

    /**
     * @inheritdoc
     */
    public function hasTax(TaxInterface $tax)
    {
        return $this->taxes->contains($tax);
    }

    /**
     * @inheritdoc
     */
    public function addTax(TaxInterface $tax)
    {
        if (!$this->hasTax($tax)) {
            $tax->addTaxRule($this);
            $this->taxes->add($tax);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeTax(TaxInterface $tax)
    {
        if ($this->hasTax($tax)) {
            $tax->removeTaxRule($this);
            $this->taxes->removeElement($tax);
        }
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setTaxes(ArrayCollection $taxes)
    {
        $this->taxes = $taxes;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @inheritdoc
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }
}
