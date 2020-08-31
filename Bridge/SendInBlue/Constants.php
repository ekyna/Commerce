<?php

namespace Ekyna\Component\Commerce\Bridge\SendInBlue;

/**
 * Class Constants
 * @package Ekyna\Component\Commerce\Bridge\SendInBlue
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Constants
{
    public const NAME = 'SendInBlue';

    public const WEBHOOK_MARKED_AS_SPAM  = 'spam';
    public const WEBHOOK_OPENED          = 'opened';
    public const WEBHOOK_CLICKED         = 'clicked';
    public const WEBHOOK_HARD_BOUNCED    = 'hard_bounce';
    public const WEBHOOK_SOFT_BOUNCED    = 'soft_bounce';
    public const WEBHOOK_DELIVERED       = 'delivered';
    public const WEBHOOK_UNSUBSCRIBED    = 'unsubscribe';
    public const WEBHOOK_CONTACT_ADDED   = 'list_addition';
    public const WEBHOOK_CONTACT_UPDATED = 'contact_updated';
    public const WEBHOOK_CONTACT_DELETED = 'contact_deleted';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
