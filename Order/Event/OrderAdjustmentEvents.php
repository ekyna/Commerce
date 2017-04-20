<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderAdjustmentEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderAdjustmentEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.order_adjustment.insert';
    public const UPDATE      = 'ekyna_commerce.order_adjustment.update';
    public const DELETE      = 'ekyna_commerce.order_adjustment.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.order_adjustment.pre_create';
    public const POST_CREATE = 'ekyna_commerce.order_adjustment.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.order_adjustment.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.order_adjustment.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.order_adjustment.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.order_adjustment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
