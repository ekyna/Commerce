<?php

namespace Ekyna\Component\Commerce\Product\Event;

/**
 * Class ProductStockUnitEvents
 * @package Ekyna\Component\Commerce\Product\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductStockUnitEvents
{
    const INSERT = 'ekyna_commerce.product_stock_unit.insert';
    const UPDATE = 'ekyna_commerce.product_stock_unit.update';
    const DELETE = 'ekyna_commerce.product_stock_unit.delete';
}
