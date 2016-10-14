<?php

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierDeliveryItemEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.supplier_delivery_item.insert';
    const UPDATE         = 'ekyna_commerce.supplier_delivery_item.update';
    const DELETE         = 'ekyna_commerce.supplier_delivery_item.delete';

    // Domain
    const PRE_CREATE     = 'ekyna_commerce.supplier_delivery_item.pre_create';
    const POST_CREATE    = 'ekyna_commerce.supplier_delivery_item.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.supplier_delivery_item.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.supplier_delivery_item.post_update';

    const PRE_DELETE     = 'ekyna_commerce.supplier_delivery_item.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.supplier_delivery_item.post_delete';
}
