<?php

namespace Ekyna\Component\Commerce\Product\Model;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Interface ProductStockUnitInterface
 * @package Ekyna\Component\Commerce\Product\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ProductStockUnitInterface extends StockUnitInterface
{
    /**
     * Returns the product.
     *
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * Sets the product.
     *
     * @param ProductInterface $product
     *
     * @return $this|ProductStockUnitInterface
     */
    public function setProduct(ProductInterface $product);
}
