<?php

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierOrderItemEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.supplier_order_item.insert';
    const UPDATE         = 'ekyna_commerce.supplier_order_item.update';
    const DELETE         = 'ekyna_commerce.supplier_order_item.delete';

    // Domain
    const CONTENT_CHANGE = 'ekyna_commerce.supplier_order_item.content_change';

    const PRE_CREATE     = 'ekyna_commerce.supplier_order_item.pre_create';
    const POST_CREATE    = 'ekyna_commerce.supplier_order_item.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.supplier_order_item.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.supplier_order_item.post_update';

    const PRE_DELETE     = 'ekyna_commerce.supplier_order_item.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.supplier_order_item.post_delete';
}
