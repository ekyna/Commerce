<?php

namespace Ekyna\Component\Commerce\Product\Builder;

use Ekyna\Component\Commerce\Common\Adapter\PersistenceAwareInterface;
use Ekyna\Component\Commerce\Product\Model\ProductInterface;

/**
 * Interface VariantBuilderInterface
 * @package Ekyna\Component\Commerce\Product\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface VariantBuilderInterface extends PersistenceAwareInterface
{
    /**
     * Builds the variant designation.
     *
     * @param ProductInterface $variant The variant product
     *
     * @return $this|VariantBuilderInterface
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function buildDesignation(ProductInterface $variant);

    /**
     * Builds the product variations.
     *
     * @param ProductInterface $product The variable product
     *
     * @return ProductInterface[]
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function buildVariations(ProductInterface $product);

    /**
     * Inherits the tax group for variable.
     *
     * @param ProductInterface $variant
     *
     * @return $this|VariantBuilderInterface
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function inheritVariableTaxGroup(ProductInterface $variant);

    /**
     * Updates the variable minimum price regarding to its variants.
     *
     * @param ProductInterface $variable
     *
     * @return $this|VariantBuilderInterface
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateVariableMinPrice(ProductInterface $variable);
}
