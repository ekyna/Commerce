<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierDeliveryEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierDeliveryEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.supplier_delivery.insert';
    public const UPDATE      = 'ekyna_commerce.supplier_delivery.update';
    public const DELETE      = 'ekyna_commerce.supplier_delivery.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.supplier_delivery.pre_create';
    public const POST_CREATE = 'ekyna_commerce.supplier_delivery.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.supplier_delivery.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.supplier_delivery.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.supplier_delivery.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.supplier_delivery.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
