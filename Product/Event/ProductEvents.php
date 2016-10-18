<?php

namespace Ekyna\Component\Commerce\Product\Event;

/**
 * Class ProductEvents
 * @package Ekyna\Component\Commerce\Product\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class ProductEvents
{
    const INSERT              = 'ekyna_commerce.product.insert';
    const UPDATE              = 'ekyna_commerce.product.update';
    const DELETE              = 'ekyna_commerce.product.delete';

    const PRE_CREATE          = 'ekyna_commerce.product.pre_create';
    const POST_CREATE         = 'ekyna_commerce.product.post_create';

    const PRE_UPDATE          = 'ekyna_commerce.product.pre_update';
    const POST_UPDATE         = 'ekyna_commerce.product.post_update';

    const PRE_DELETE          = 'ekyna_commerce.product.pre_delete';
    const POST_DELETE         = 'ekyna_commerce.product.post_delete';

    const STOCK_UNIT_CHANGE   = 'ekyna_commerce.product.stock_unit_change';
    const CHILD_STOCK_CHANGE  = 'ekyna_commerce.product.child_stock_change';
}
