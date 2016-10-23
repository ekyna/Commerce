<?php

namespace Ekyna\Component\Commerce\Cart\Event;

/**
 * Class CartAddressEvents
 * @package Ekyna\Component\Commerce\Cart\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartAddressEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.cart_address.insert';
    const UPDATE         = 'ekyna_commerce.cart_address.update';
    const DELETE         = 'ekyna_commerce.cart_address.delete';

    // Domain
    const PRE_CREATE     = 'ekyna_commerce.cart_address.pre_create';
    const POST_CREATE    = 'ekyna_commerce.cart_address.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.cart_address.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.cart_address.post_update';

    const PRE_DELETE     = 'ekyna_commerce.cart_address.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.cart_address.post_delete';
}
