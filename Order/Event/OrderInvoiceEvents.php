<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderInvoiceEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderInvoiceEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.order_invoice.insert';
    public const UPDATE         = 'ekyna_commerce.order_invoice.update';
    public const DELETE         = 'ekyna_commerce.order_invoice.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.order_invoice.state_change';
    public const CONTENT_CHANGE = 'ekyna_commerce.order_invoice.content_change';

    public const PRE_CREATE     = 'ekyna_commerce.order_invoice.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.order_invoice.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.order_invoice.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.order_invoice.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.order_invoice.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.order_invoice.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
