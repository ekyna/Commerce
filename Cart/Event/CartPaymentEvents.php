<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Event;

/**
 * Class CartPaymentEvents
 * @package Ekyna\Component\Commerce\Cart\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartPaymentEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.cart_payment.insert';
    public const UPDATE         = 'ekyna_commerce.cart_payment.update';
    public const DELETE         = 'ekyna_commerce.cart_payment.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.cart_payment.state_change';
    public const CONTENT_CHANGE = 'ekyna_commerce.cart_payment.content_change';

    public const PRE_CREATE     = 'ekyna_commerce.cart_payment.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.cart_payment.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.cart_payment.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.cart_payment.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.cart_payment.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.cart_payment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
