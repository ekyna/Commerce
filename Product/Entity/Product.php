<?php

namespace Ekyna\Component\Commerce\Product\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;
use Ekyna\Component\Commerce\Product\Model as Model;

/**
 * Class Product
 * @package Ekyna\Component\Commerce\Product\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Product implements Model\ProductInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var Model\ProductInterface
     */
    protected $parent;

    /**
     * @var ArrayCollection|Model\ProductInterface[]
     */
    protected $variants;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var Model\AttributeSetInterface
     */
    protected $attributeSet;

    /**
     * @var ArrayCollection|Model\AttributeInterface[]
     */
    protected $attributes;

    /**
     * @var ArrayCollection|Model\OptionGroupInterface[]
     */
    protected $optionGroups;

    /**
     * @var ArrayCollection|Model\BundleSlotInterface[]
     */
    protected $bundleSlots;

    /**
     * @var string
     */
    protected $designation;

    /**
     * @var string
     */
    protected $reference;

    /**
     * @var float
     */
    protected $netPrice;

    /**
     * @var TaxGroupInterface
     */
    protected $taxGroup;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->variants = new ArrayCollection();
        $this->attributes = new ArrayCollection();
        $this->optionGroups = new ArrayCollection();
        $this->bundleSlots = new ArrayCollection();
    }

    /**
     * Clones the product.
     */
    public function __clone()
    {
        $variants = $this->variants;
        $this->variants = new ArrayCollection();
        foreach ($variants as $variant) {
            $this->addVariant(clone $variant);
        }

        $attributes = $this->attributes;
        $this->attributes = new ArrayCollection();
        foreach ($attributes as $attribute) {
            $this->addAttribute(clone $attribute);
        }

        $optionGroups = $this->optionGroups;
        $this->optionGroups = new ArrayCollection();
        foreach ($optionGroups as $optionGroup) {
            $this->addOptionGroup(clone $optionGroup);
        }

        $bundleSlots = $this->bundleSlots;
        $this->bundleSlots = new ArrayCollection();
        foreach ($bundleSlots as $bundleSlot) {
            $this->addBundleSlot(clone $bundleSlot);
        }
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->type === Model\ProductTypes::TYPE_VARIANT) {
            return sprintf('%s %s', $this->parent->getDesignation(), $this->getDesignation());
        }

        return $this->getDesignation();
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
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function setParent(Model\ProductInterface $parent = null)
    {
        if (null !== $this->parent && $parent !== $this->parent) {
            $this->parent->removeVariant($this);
        }

        $this->parent = $parent;

        if (null !== $parent) {
            $parent->addVariant($this);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @inheritdoc
     */
    public function hasVariant(Model\ProductInterface $variant)
    {
        return $this->variants->contains($variant);
    }

    /**
     * @inheritdoc
     */
    public function addVariant(Model\ProductInterface $variant)
    {
        if (!$this->hasVariant($variant)) {
            if ($variant->getParent() !== $this) {
                $variant->setParent($this);
            }
            $this->variants->add($variant);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeVariant(Model\ProductInterface $variant)
    {
        if ($this->hasVariant($variant)) {
            $variant->setParent(null);
            $this->variants->removeElement($variant);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    /*public function setVariants(ArrayCollection $variants)
    {
        foreach ($this->variants as $variant) {
            $this->removeVariant($variant);
        }

        foreach ($variants as $variant) {
            $this->addVariant($variant);
        }

        return $this;
    }*/

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributeSet()
    {
        return $this->attributeSet;
    }

    /**
     * @inheritdoc
     */
    public function setAttributeSet(Model\AttributeSetInterface $attributeSet = null)
    {
        $this->attributeSet = $attributeSet;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function hasAttribute(Model\AttributeInterface $attribute)
    {
        return $this->attributes->contains($attribute);
    }

    /**
     * @inheritdoc
     */
    public function addAttribute(Model\AttributeInterface $attribute)
    {
        if (!$this->hasAttribute($attribute)) {
            $this->attributes->add($attribute);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeAttribute(Model\AttributeInterface $attribute)
    {
        if ($this->hasAttribute($attribute)) {
            $this->attributes->removeElement($attribute);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getOptionGroups()
    {
        return $this->optionGroups;
    }

    /**
     * @inheritdoc
     */
    public function hasOptionGroup(Model\OptionGroupInterface $group)
    {
        return $this->optionGroups->contains($group);
    }

    /**
     * @inheritdoc
     */
    public function addOptionGroup(Model\OptionGroupInterface $group)
    {
        if (!$this->hasOptionGroup($group)) {
            $group->setProduct($this);
            $this->optionGroups->add($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeOptionGroup(Model\OptionGroupInterface $group)
    {
        if ($this->hasOptionGroup($group)) {
            $group->setProduct(null);
            $this->optionGroups->removeElement($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOptionGroups(ArrayCollection $optionGroups)
    {
        foreach ($this->optionGroups as $group) {
            $this->removeOptionGroup($group);
        }

        foreach ($optionGroups as $group) {
            $this->addOptionGroup($group);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getBundleSlots()
    {
        return $this->bundleSlots;
    }

    /**
     * @inheritdoc
     */
    public function hasBundleSlot(Model\BundleSlotInterface $slot)
    {
        return $this->bundleSlots->contains($slot);
    }

    /**
     * @inheritdoc
     */
    public function addBundleSlot(Model\BundleSlotInterface $slot)
    {
        if (!$this->hasBundleSlot($slot)) {
            $slot->setBundle($this);
            $this->bundleSlots->add($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function removeBundleSlot(Model\BundleSlotInterface $slot)
    {
        if ($this->hasBundleSlot($slot)) {
            $slot->setBundle(null);
            $this->bundleSlots->removeElement($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setBundleSlots(ArrayCollection $bundleSlots)
    {
        foreach ($this->bundleSlots as $slot) {
            $this->removeBundleSlot($slot);
        }

        foreach ($bundleSlots as $slot) {
            $this->addBundleSlot($slot);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDesignation()
    {
        return $this->designation;
    }

    /**
     * @inheritdoc
     */
    public function setDesignation($designation)
    {
        $this->designation = $designation;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @inheritdoc
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @inheritdoc
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getTaxGroup()
    {
        return $this->taxGroup;
    }

    /**
     * @inheritdoc
     */
    public function setTaxGroup(TaxGroupInterface $group)
    {
        $this->taxGroup = $group;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUniquenessSignature()
    {
        Model\ProductTypes::assertVariant($this);

        $ids = [];
        foreach ($this->attributes as $attribute) {
            $ids[] = $attribute->getId();
        }
        sort($ids);

        return implode('-', $ids);
    }
}