<?php

namespace Ekyna\Component\Commerce\Newsletter\Model;

/**
 * Class Status
 * @package Ekyna\Component\Commerce\Newsletter\Model
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class SubscriptionStatus
{
    public const SUBSCRIBED   = 'subscribed';
    public const UNSUBSCRIBED = 'unsubscribed';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
