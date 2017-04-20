<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Cart\Event;

/**
 * Class CartAdjustmentEvents
 * @package Ekyna\Component\Commerce\Cart\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class CartAdjustmentEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.cart_adjustment.insert';
    public const UPDATE      = 'ekyna_commerce.cart_adjustment.update';
    public const DELETE      = 'ekyna_commerce.cart_adjustment.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.cart_adjustment.pre_create';
    public const POST_CREATE = 'ekyna_commerce.cart_adjustment.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.cart_adjustment.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.cart_adjustment.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.cart_adjustment.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.cart_adjustment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
