<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderInvoiceLineEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderInvoiceLineEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.order_invoice_line.insert';
    const UPDATE      = 'ekyna_commerce.order_invoice_line.update';
    const DELETE      = 'ekyna_commerce.order_invoice_line.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.order_invoice_line.initialize';

    const PRE_CREATE  = 'ekyna_commerce.order_invoice_line.pre_create';
    const POST_CREATE = 'ekyna_commerce.order_invoice_line.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.order_invoice_line.pre_update';
    const POST_UPDATE = 'ekyna_commerce.order_invoice_line.post_update';

    const PRE_DELETE  = 'ekyna_commerce.order_invoice_line.pre_delete';
    const POST_DELETE = 'ekyna_commerce.order_invoice_line.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
