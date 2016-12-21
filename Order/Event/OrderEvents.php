<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.order.insert';
    const UPDATE         = 'ekyna_commerce.order.update';
    const DELETE         = 'ekyna_commerce.order.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.order.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.order.content_change';
    const ADDRESS_CHANGE = 'ekyna_commerce.order.address_change';

    const PRE_CREATE     = 'ekyna_commerce.order.pre_create';
    const POST_CREATE    = 'ekyna_commerce.order.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.order.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.order.post_update';

    const PRE_DELETE     = 'ekyna_commerce.order.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.order.post_delete';
}
