<?php

namespace Ekyna\Component\Commerce\Product\Updater;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;

/**
 * Interface ProductUpdaterInterface
 * @package Ekyna\Component\Commerce\Product\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductUpdaterInterface
{
    /**
     * Updates the variant designation regarding to his attributes.
     *
     * @param ProductInterface $variant The variant product
     *
     * @return bool Whether the variable has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateVariantDesignation(ProductInterface $variant);

    /**
     * Updates the tax group regarding to his parent/variable product.
     *
     * @param ProductInterface $variant
     *
     * @return bool Whether the variable has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateVariantTaxGroup(ProductInterface $variant);

    /**
     * Updates the variable minimum price regarding to its variants.
     *
     * @param ProductInterface $variable
     *
     * @return bool Whether the variable has been changed or not.
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function updateVariableMinPrice(ProductInterface $variable);
}
