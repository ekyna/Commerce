<?php

namespace Ekyna\Component\Commerce\Product\Updater;

use Ekyna\Component\Commerce\Exception\InvalidProductException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;
use Ekyna\Component\Commerce\Product\Model\ProductTypes;

/**
 * Class ProductUpdater
 * @package Ekyna\Component\Commerce\Product\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductUpdater implements ProductUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function updateVariantDesignation(ProductInterface $variant)
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

        $designation = implode(' ', $attributeNames);
        if ($designation != $variant->getDesignation()) {
            $variant->setDesignation($designation);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateVariantTaxGroup(ProductInterface $variant)
    {
        $this->assertVariantWithParent($variant);

        $taxGroup = $variant->getParent()->getTaxGroup();
        if ($variant->getTaxGroup() !== $taxGroup) {
            $variant->setTaxGroup($taxGroup);

            return true;
        }

        return false;
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

            return true;
        }

        return false;
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
}
