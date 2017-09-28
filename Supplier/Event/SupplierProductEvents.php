<?php

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierProductEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierProductEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.supplier_product.insert';
    const UPDATE      = 'ekyna_commerce.supplier_product.update';
    const DELETE      = 'ekyna_commerce.supplier_product.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.supplier_product.initialize';

    const PRE_CREATE  = 'ekyna_commerce.supplier_product.pre_create';
    const POST_CREATE = 'ekyna_commerce.supplier_product.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.supplier_product.pre_update';
    const POST_UPDATE = 'ekyna_commerce.supplier_product.post_update';

    const PRE_DELETE  = 'ekyna_commerce.supplier_product.pre_delete';
    const POST_DELETE = 'ekyna_commerce.supplier_product.post_delete';
}
