<?php

namespace Ekyna\Component\Commerce\Cart\Event;

/**
 * Class CartItemEvents
 * @package Ekyna\Component\Commerce\Cart\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartItemEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.cart_item.insert';
    const UPDATE         = 'ekyna_commerce.cart_item.update';
    const DELETE         = 'ekyna_commerce.cart_item.delete';

    // Domain
    const PRE_CREATE     = 'ekyna_commerce.cart_item.pre_create';
    const POST_CREATE    = 'ekyna_commerce.cart_item.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.cart_item.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.cart_item.post_update';

    const PRE_DELETE     = 'ekyna_commerce.cart_item.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.cart_item.post_delete';
}
