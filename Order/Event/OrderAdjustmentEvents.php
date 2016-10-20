<?php

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderAdjustmentEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderAdjustmentEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.order_adjustment.insert';
    const UPDATE         = 'ekyna_commerce.order_adjustment.update';
    const DELETE         = 'ekyna_commerce.order_adjustment.delete';

    // Domain
    const PRE_CREATE     = 'ekyna_commerce.order_adjustment.pre_create';
    const POST_CREATE    = 'ekyna_commerce.order_adjustment.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.order_adjustment.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.order_adjustment.post_update';

    const PRE_DELETE     = 'ekyna_commerce.order_adjustment.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.order_adjustment.post_delete';
}
