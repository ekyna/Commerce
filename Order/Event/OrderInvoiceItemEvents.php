<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderInvoiceItemEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderInvoiceItemEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.order_invoice_item.insert';
    const UPDATE      = 'ekyna_commerce.order_invoice_item.update';
    const DELETE      = 'ekyna_commerce.order_invoice_item.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.order_invoice_item.initialize';

    const PRE_CREATE  = 'ekyna_commerce.order_invoice_item.pre_create';
    const POST_CREATE = 'ekyna_commerce.order_invoice_item.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.order_invoice_item.pre_update';
    const POST_UPDATE = 'ekyna_commerce.order_invoice_item.post_update';

    const PRE_DELETE  = 'ekyna_commerce.order_invoice_item.pre_delete';
    const POST_DELETE = 'ekyna_commerce.order_invoice_item.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
