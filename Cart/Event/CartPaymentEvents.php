<?php

namespace Ekyna\Component\Commerce\Cart\Event;

/**
 * Class CartPaymentEvents
 * @package Ekyna\Component\Commerce\Cart\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartPaymentEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.cart_payment.insert';
    const UPDATE         = 'ekyna_commerce.cart_payment.update';
    const DELETE         = 'ekyna_commerce.cart_payment.delete';

    // Domain
    const STATE_CHANGE   = 'ekyna_commerce.cart_payment.state_change';
    const CONTENT_CHANGE = 'ekyna_commerce.cart_payment.content_change';

    const INITIALIZE     = 'ekyna_commerce.cart_payment.initialize';

    const PRE_CREATE     = 'ekyna_commerce.cart_payment.pre_create';
    const POST_CREATE    = 'ekyna_commerce.cart_payment.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.cart_payment.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.cart_payment.post_update';

    const PRE_DELETE     = 'ekyna_commerce.cart_payment.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.cart_payment.post_delete';
}
