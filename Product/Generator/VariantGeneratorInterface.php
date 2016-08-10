<?php

namespace Ekyna\Component\Commerce\Product\Generator;

use Ekyna\Component\Commerce\Product\Model\ProductInterface;

/**
 * Interface VariantGeneratorInterface
 * @package Ekyna\Component\Commerce\Product\Generator
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface VariantGeneratorInterface
{
    /**
     * Generates the variants for the given variable product.
     *
     * @param ProductInterface $product The variable product
     *
     * @return ProductInterface[] The generated variants
     * @throws \Ekyna\Component\Commerce\Exception\CommerceExceptionInterface
     */
    public function generateVariants(ProductInterface $product);
}
