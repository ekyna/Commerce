<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderAddressEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderAddressEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.order_address.insert';
    public const UPDATE      = 'ekyna_commerce.order_address.update';
    public const DELETE      = 'ekyna_commerce.order_address.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.order_address.pre_create';
    public const POST_CREATE = 'ekyna_commerce.order_address.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.order_address.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.order_address.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.order_address.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.order_address.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
