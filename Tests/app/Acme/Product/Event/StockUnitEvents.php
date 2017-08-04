<?php

namespace Acme\Product\Event;

/**
 * Class StockUnitEvents
 * @package Acme\Product\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockUnitEvents
{
    const INSERT = 'acme_product.stock_unit.insert';
    const UPDATE = 'acme_product.stock_unit.update';
    const DELETE = 'acme_product.stock_unit.delete';
}
