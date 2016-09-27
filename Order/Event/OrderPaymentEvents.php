<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderPaymentEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderPaymentEvents
{
    // Persistence
    const INSERT = 'ekyna_commerce.order_payment.insert';
    const UPDATE = 'ekyna_commerce.order_payment.update';
    const DELETE = 'ekyna_commerce.order_payment.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.order_payment.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.order_payment.content_change';

    const PRE_CREATE     = 'ekyna_commerce.order_payment.pre_create';
    const POST_CREATE    = 'ekyna_commerce.order_payment.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.order_payment.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.order_payment.post_update';

    const PRE_DELETE     = 'ekyna_commerce.order_payment.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.order_payment.post_delete';
}
