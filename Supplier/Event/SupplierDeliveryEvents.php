<?php

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierDeliveryEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.supplier_delivery.insert';
    const UPDATE      = 'ekyna_commerce.supplier_delivery.update';
    const DELETE      = 'ekyna_commerce.supplier_delivery.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.supplier_delivery.initialize';

    const PRE_CREATE  = 'ekyna_commerce.supplier_delivery.pre_create';
    const POST_CREATE = 'ekyna_commerce.supplier_delivery.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.supplier_delivery.pre_update';
    const POST_UPDATE = 'ekyna_commerce.supplier_delivery.post_update';

    const PRE_DELETE  = 'ekyna_commerce.supplier_delivery.pre_delete';
    const POST_DELETE = 'ekyna_commerce.supplier_delivery.post_delete';
}
