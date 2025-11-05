<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderItemAssignmentEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderItemAssignmentEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.order_item_assignment.insert';
    public const UPDATE      = 'ekyna_commerce.order_item_assignment.update';
    public const DELETE      = 'ekyna_commerce.order_item_assignment.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.order_item_assignment.pre_create';
    public const POST_CREATE = 'ekyna_commerce.order_item_assignment.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.order_item_assignment.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.order_item_assignment.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.order_item_assignment.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.order_item_assignment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
