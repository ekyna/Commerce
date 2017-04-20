<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierOrderItemEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderItemEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.supplier_order_item.insert';
    public const UPDATE         = 'ekyna_commerce.supplier_order_item.update';
    public const DELETE         = 'ekyna_commerce.supplier_order_item.delete';

    // Domain
    public const CONTENT_CHANGE = 'ekyna_commerce.supplier_order_item.content_change';

    public const PRE_CREATE     = 'ekyna_commerce.supplier_order_item.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.supplier_order_item.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.supplier_order_item.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.supplier_order_item.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.supplier_order_item.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.supplier_order_item.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
