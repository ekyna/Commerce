<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierDeliveryItemEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierDeliveryItemEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.supplier_delivery_item.insert';
    public const UPDATE      = 'ekyna_commerce.supplier_delivery_item.update';
    public const DELETE      = 'ekyna_commerce.supplier_delivery_item.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.supplier_delivery_item.pre_create';
    public const POST_CREATE = 'ekyna_commerce.supplier_delivery_item.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.supplier_delivery_item.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.supplier_delivery_item.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.supplier_delivery_item.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.supplier_delivery_item.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
