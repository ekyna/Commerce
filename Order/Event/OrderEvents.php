<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Event;

/**
 * Class OrderEvents
 * @package Ekyna\Component\Commerce\Order\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class OrderEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.order.insert';
    public const UPDATE         = 'ekyna_commerce.order.update';
    public const DELETE         = 'ekyna_commerce.order.delete';

    // Domain
    public const STATE_CHANGE   = 'ekyna_commerce.order.state_change';
    public const CONTENT_CHANGE = 'ekyna_commerce.order.content_change';
    public const ADDRESS_CHANGE = 'ekyna_commerce.order.address_change';

    public const PREPARE        = 'ekyna_commerce.order.prepare';

    public const PRE_CREATE     = 'ekyna_commerce.order.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.order.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.order.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.order.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.order.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.order.post_delete';

    public const COMPLETED      = 'ekyna_commerce.order.completed';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}

