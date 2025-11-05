<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Supplier\Event;

/**
 * Class SupplierOrderEvents
 * @package Ekyna\Component\Commerce\Supplier\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class SupplierOrderEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.supplier_order.insert';
    public const UPDATE         = 'ekyna_commerce.supplier_order.update';
    public const DELETE         = 'ekyna_commerce.supplier_order.delete';

    // Domain
    public const CONTENT_CHANGE = 'ekyna_commerce.supplier_order.content_change';
    public const STATE_CHANGE   = 'ekyna_commerce.supplier_order.state_change';

    public const PRE_CREATE     = 'ekyna_commerce.supplier_order.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.supplier_order.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.supplier_order.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.supplier_order.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.supplier_order.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.supplier_order.post_delete';

    public const PRE_SUBMIT     = 'ekyna_commerce.supplier_order.pre_submit';
    public const POST_SUBMIT    = 'ekyna_commerce.supplier_order.post_submit';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
