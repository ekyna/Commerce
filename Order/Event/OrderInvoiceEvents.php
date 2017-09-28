<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderInvoiceEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderInvoiceEvents
{
    // Persistence
    const INSERT = 'ekyna_commerce.order_invoice.insert';
    const UPDATE = 'ekyna_commerce.order_invoice.update';
    const DELETE = 'ekyna_commerce.order_invoice.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.order_invoice.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.order_invoice.content_change';

    const INITIALIZE     = 'ekyna_commerce.order_invoice.initialize';

    const PRE_CREATE     = 'ekyna_commerce.order_invoice.pre_create';
    const POST_CREATE    = 'ekyna_commerce.order_invoice.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.order_invoice.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.order_invoice.post_update';

    const PRE_DELETE     = 'ekyna_commerce.order_invoice.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.order_invoice.post_delete';
}
