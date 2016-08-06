<?php

namespace Ekyna\Component\Commerce\Product\Builder;

use Ekyna\Component\Commerce\Common\Adapter\PersistenceAwareTrait;
use Ekyna\Component\Commerce\Exception\InvalidProductException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;

/**
 * Class VariantBuilder
 * @package Ekyna\Component\Commerce\Product\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class VariantBuilder implements VariantBuilderInterface
{
    use PersistenceAwareTrait;

    /**
     * @var string
     */
    protected $productClass;


    /**
     * Constructor.
     *
     * @param string $productClass
     */
    public function __construct($productClass)
    {
        $this->productClass = $productClass;
    }

    /**
     * Asserts that the variant has a parent.
     *
     * @param ProductInterface $variant
     */
    protected function assertVariantWithParent(ProductInterface $variant)
    {
        ProductTypes::assertVariant($variant);

        if (null === $variant->getParent()) {
            throw new RuntimeException("Variant's parent must be defined.");
        }
    }

    /**
     * @inheritdoc
     */
    public function buildDesignation(ProductInterface $variant)
    {
        $this->assertVariantWithParent($variant);

        if (null === $attributeSet = $variant->getParent()->getAttributeSet()) {
            throw new RuntimeException("Variant's parent attribute set must be defined.");
        }

        $attributeNames = [];
        foreach ($attributeSet->getSlots() as $slot) {
            $group = $slot->getGroup();
            $found = false;
            foreach ($variant->getAttributes() as $attribute) {
                if ($attribute->getGroup() === $group) {
                    $attributeNames[] = $attribute->getName();
                    $found = true;
                    if (!$slot->isMultiple()) {
                        continue 2;
                    }
                }
            }
            if (!$found) {
                throw new InvalidProductException("No attribute found for attribute group '$group'.'");
            }
        }

        $variant->setDesignation(implode(' ', $attributeNames));

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function buildVariations(ProductInterface $variable)
    {
        ProductTypes::assertVariable($variable);

        if (null === $attributeSet = $variable->getAttributeSet()) {
            throw new RuntimeException("Variable attribute set must be defined.");
        }

        $variants = [];

        foreach ($attributeSet->getSlots() as $slot) {
            $attributes = $slot->getGroup()->getAttributes();

            // First pass : create initial variants
            if (empty($variants)) {
                foreach ($attributes as $attribute) {
                    /** @var ProductInterface $variant */
                    $variant = new $this->productClass();
                    $variant
                        ->setType(ProductTypes::TYPE_VARIANT)
                        ->addAttribute($attribute);

                    $variants[] = $variant;
                }
                continue;
            }

            $tmp = [];

            // Next passes : clone variants to preserve previous pass variants.
            foreach ($attributes as $attribute) {
                foreach ($variants as $variant) {
                    $clone = clone $variant;
                    $tmp[] = $clone->addAttribute($attribute);
                }
            }

            $variants = $tmp;
        }

        return $variants;
    }

    /**
     * @inheritdoc
     */
    public function inheritVariableTaxGroup(ProductInterface $variant)
    {
        $this->assertVariantWithParent($variant);

        $taxGroup = $variant->getParent()->getTaxGroup();
        if ($variant->getTaxGroup() !== $taxGroup) {
            $variant->setTaxGroup($taxGroup);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function updateVariableMinPrice(ProductInterface $variable)
    {
        ProductTypes::assertVariable($variable);

        $variants = $variable->getVariants()->getIterator();
        if (0 == count($variants)) {
            return $this;
        }

        $minPrice = null;
        foreach ($variants as $variant) {
            if (null === $minPrice || $minPrice > $variant->getNetPrice()) {
                $minPrice = $variant->getNetPrice();
            }
        }

        if (null !== $minPrice && 0 !== bccomp($variable->getNetPrice(), $minPrice, 5)) {
            $variable->setNetPrice($minPrice);

            $this->persist($variable);
        }

        return $this;
    }
}
