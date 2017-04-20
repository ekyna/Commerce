<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderInvoiceLineEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderInvoiceLineEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.order_invoice_line.insert';
    public const UPDATE      = 'ekyna_commerce.order_invoice_line.update';
    public const DELETE      = 'ekyna_commerce.order_invoice_line.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.order_invoice_line.pre_create';
    public const POST_CREATE = 'ekyna_commerce.order_invoice_line.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.order_invoice_line.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.order_invoice_line.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.order_invoice_line.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.order_invoice_line.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
