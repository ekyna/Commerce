<?php

namespace Ekyna\Component\Commerce\Cart\Event;

/**
 * Class CartEvents
 * @package Ekyna\Component\Commerce\Cart\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.cart.insert';
    const UPDATE         = 'ekyna_commerce.cart.update';
    const DELETE         = 'ekyna_commerce.cart.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.cart.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.cart.content_change';

    const PRE_CREATE     = 'ekyna_commerce.cart.pre_create';
    const POST_CREATE    = 'ekyna_commerce.cart.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.cart.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.cart.post_update';

    const PRE_DELETE     = 'ekyna_commerce.cart.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.cart.post_delete';
}
