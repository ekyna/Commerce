<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderCreditEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderCreditEvents
{
    // Persistence
    const INSERT = 'ekyna_commerce.order_credit.insert';
    const UPDATE = 'ekyna_commerce.order_credit.update';
    const DELETE = 'ekyna_commerce.order_credit.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.order_credit.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.order_credit.content_change';

    const PRE_CREATE     = 'ekyna_commerce.order_credit.pre_create';
    const POST_CREATE    = 'ekyna_commerce.order_credit.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.order_credit.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.order_credit.post_update';

    const PRE_DELETE     = 'ekyna_commerce.order_credit.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.order_credit.post_delete';
}
