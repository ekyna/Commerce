<?php

namespace Ekyna\Component\Commerce\Product\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Ekyna\Component\Commerce\Common\Model\EntityInterface;
use Ekyna\Component\Commerce\Pricing\Model\TaxGroupInterface;

/**
 * Interface ProductInterface
 * @package Ekyna\Component\Commerce\Product\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductInterface extends EntityInterface
{
    /**
     * Sets the id.
     *
     * @param int $id
     *
     * @return $this|ProductInterface
     */
    public function setId($id);

    /**
     * Returns the parent.
     *
     * @return ProductInterface
     */
    public function getParent();

    /**
     * Sets the parent.
     *
     * @param ProductInterface $parent
     *
     * @return $this|ProductInterface
     */
    public function setParent(ProductInterface $parent = null);

    /**
     * Returns the variants.
     *
     * @return ArrayCollection|ProductInterface[]
     */
    public function getVariants();

    /**
     * Returns whether the parent has the variant or not.
     *
     * @param ProductInterface $variant
     *
     * @return bool
     */
    public function hasVariant(ProductInterface $variant);

    /**
     * Adds the variant.
     *
     * @param ProductInterface $variant
     *
     * @return $this|ProductInterface
     */
    public function addVariant(ProductInterface $variant);

    /**
     * Removes the variant.
     *
     * @param ProductInterface $variant
     *
     * @return $this|ProductInterface
     */
    public function removeVariant(ProductInterface $variant);

    /**
     * Sets the variants.
     *
     * @param ArrayCollection|ProductInterface[] $variants
     *
     * @return $this|ProductInterface
     * @internal
     */
    //public function setVariants(ArrayCollection $variants);

    /**
     * Returns the type.
     *
     * @return string
     */
    public function getType();

    /**
     * Sets the type.
     *
     * @param string $type
     *
     * @return $this|ProductInterface
     */
    public function setType($type);

    /**
     * Returns the attribute set.
     *
     * @return AttributeSetInterface
     */
    public function getAttributeSet();

    /**
     * Sets the attribute set.
     *
     * @param AttributeSetInterface $attributeSet
     *
     * @return $this|ProductInterface
     */
    public function setAttributeSet(AttributeSetInterface $attributeSet = null);

    /**
     * Returns the attributes.
     *
     * @return ArrayCollection|AttributeInterface[]
     */
    public function getAttributes();

    /**
     * Returns whether the product has the attribute or not.
     *
     * @param AttributeInterface $attribute
     *
     * @return bool
     */
    public function hasAttribute(AttributeInterface $attribute);

    /**
     * Adds the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return $this|ProductInterface
     */
    public function addAttribute(AttributeInterface $attribute);

    /**
     * Removes the attribute.
     *
     * @param AttributeInterface $attribute
     *
     * @return $this|ProductInterface
     */
    public function removeAttribute(AttributeInterface $attribute);

    /**
     * Sets the attributes.
     *
     * @param ArrayCollection|AttributeInterface[] $attributes
     *
     * @return $this|ProductInterface
     */
    public function setAttributes(ArrayCollection $attributes);

    /**
     * Returns the option groups.
     *
     * @return ArrayCollection|OptionGroupInterface[]
     */
    public function getOptionGroups();

    /**
     * Returns whether the product has the option group or not.
     *
     * @param OptionGroupInterface $group
     *
     * @return bool
     */
    public function hasOptionGroup(OptionGroupInterface $group);

    /**
     * Adds the option group.
     *
     * @param OptionGroupInterface $group
     *
     * @return $this|ProductInterface
     */
    public function addOptionGroup(OptionGroupInterface $group);

    /**
     * Removes the option group.
     *
     * @param OptionGroupInterface $group
     *
     * @return $this|ProductInterface
     */
    public function removeOptionGroup(OptionGroupInterface $group);

    /**
     * Sets the option  groups.
     *
     * @param ArrayCollection|OptionGroupInterface[] $options
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setOptionGroups(ArrayCollection $options);

    /**
     * Returns the bundle slots.
     *
     * @return ArrayCollection|BundleSlotInterface[]
     */
    public function getBundleSlots();

    /**
     * Returns whether the product has the bundle slot or not.
     *
     * @param BundleSlotInterface $slot
     *
     * @return bool
     */
    public function hasBundleSlot(BundleSlotInterface $slot);

    /**
     * Adds the bundle slot.
     *
     * @param BundleSlotInterface $slot
     *
     * @return $this|ProductInterface
     */
    public function addBundleSlot(BundleSlotInterface $slot);

    /**
     * Removes the bundle slot.
     *
     * @param BundleSlotInterface $slot
     *
     * @return $this|ProductInterface
     */
    public function removeBundleSlot(BundleSlotInterface $slot);

    /**
     * Sets the bundle slots.
     *
     * @param ArrayCollection|BundleSlotInterface[] $bundleSlots
     *
     * @return $this|ProductInterface
     * @internal
     */
    public function setBundleSlots(ArrayCollection $bundleSlots);

    /**
     * Returns the designation.
     *
     * @return string
     */
    public function getDesignation();

    /**
     * Sets the designation.
     *
     * @param string $designation
     *
     * @return $this|ProductInterface
     */
    public function setDesignation($designation);

    /**
     * Returns the reference.
     *
     * @return string
     */
    public function getReference();

    /**
     * Sets the reference.
     *
     * @param string $reference
     *
     * @return $this|ProductInterface
     */
    public function setReference($reference);

    /**
     * Returns the netPrice.
     *
     * @return float
     */
    public function getNetPrice();

    /**
     * Sets the netPrice.
     *
     * @param float $netPrice
     *
     * @return $this|ProductInterface
     */
    public function setNetPrice($netPrice);

    /**
     * Returns the tax group.
     *
     * @return TaxGroupInterface
     */
    public function getTaxGroup();

    /**
     * Sets the tax group.
     *
     * @param TaxGroupInterface $group
     *
     * @return $this|ProductInterface
     */
    public function setTaxGroup(TaxGroupInterface $group);

    /**
     * Returns the "created at" datetime.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Sets the "created at" datetime.
     *
     * @param \DateTime $createdAt
     *
     * @return $this|ProductInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * Returns the "updated at" datetime.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Sets the "updated at" datetime.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this|ProductInterface
     */
    public function setUpdatedAt(\DateTime $updatedAt = null);

    /**
     * Returns the variant uniqueness signature.
     *
     * @return string
     */
    public function getUniquenessSignature();
}
