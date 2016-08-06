<?php

namespace Ekyna\Component\Commerce\Product\Model;

/**
 * Interface ProductEventInterface
 * @package Ekyna\Component\Commerce\Product\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductEventInterface
{
    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct();
}
