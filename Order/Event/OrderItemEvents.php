<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderItemEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderItemEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.order_item.insert';
    public const UPDATE      = 'ekyna_commerce.order_item.update';
    public const DELETE      = 'ekyna_commerce.order_item.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.order_item.pre_create';
    public const POST_CREATE = 'ekyna_commerce.order_item.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.order_item.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.order_item.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.order_item.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.order_item.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
