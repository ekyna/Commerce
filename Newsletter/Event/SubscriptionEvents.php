<?php

namespace Ekyna\Component\Commerce\Newsletter\Event;

/**
 * Class SubscriptionEvents
 * @package Ekyna\Component\Commerce\Newsletter\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionEvents
{
    // Persistence
    const INSERT      = 'ekyna_commerce.subscription.insert';
    const UPDATE      = 'ekyna_commerce.subscription.update';
    const DELETE      = 'ekyna_commerce.subscription.delete';

    // Domain
    const INITIALIZE  = 'ekyna_commerce.subscription.initialize';

    const PRE_CREATE  = 'ekyna_commerce.subscription.pre_create';
    const POST_CREATE = 'ekyna_commerce.subscription.post_create';

    const PRE_UPDATE  = 'ekyna_commerce.subscription.pre_update';
    const POST_UPDATE = 'ekyna_commerce.subscription.post_update';

    const PRE_DELETE  = 'ekyna_commerce.subscription.pre_delete';
    const POST_DELETE = 'ekyna_commerce.subscription.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
