<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Event;

/**
 * Class SubscriptionEvents
 * @package Ekyna\Component\Commerce\Newsletter\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.subscription.insert';
    public const UPDATE      = 'ekyna_commerce.subscription.update';
    public const DELETE      = 'ekyna_commerce.subscription.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.subscription.pre_create';
    public const POST_CREATE = 'ekyna_commerce.subscription.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.subscription.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.subscription.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.subscription.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.subscription.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
