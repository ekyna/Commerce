<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Event;

/**
 * Class CartEvents
 * @package Ekyna\Component\Commerce\Cart\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.cart.insert';
    public const UPDATE         = 'ekyna_commerce.cart.update';
    public const DELETE         = 'ekyna_commerce.cart.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.cart.state_change';
    public const CONTENT_CHANGE = 'ekyna_commerce.cart.content_change';
    public const ADDRESS_CHANGE = 'ekyna_commerce.cart.address_change';

    public const PRE_CREATE     = 'ekyna_commerce.cart.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.cart.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.cart.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.cart.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.cart.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.cart.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
