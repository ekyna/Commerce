<?php

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierOrderEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.supplier_order.insert';
    const UPDATE         = 'ekyna_commerce.supplier_order.update';
    const DELETE         = 'ekyna_commerce.supplier_order.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.supplier_order.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.supplier_order.content_change';

    const PRE_CREATE     = 'ekyna_commerce.supplier_order.pre_create';
    const POST_CREATE    = 'ekyna_commerce.supplier_order.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.supplier_order.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.supplier_order.post_update';

    const PRE_DELETE     = 'ekyna_commerce.supplier_order.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.supplier_order.post_delete';
}
