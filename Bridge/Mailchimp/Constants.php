<?php

namespace Ekyna\Component\Commerce\Bridge\Mailchimp;

/**
 * Class Constants
 * @package Ekyna\Component\Commerce\Bridge\Mailchimp
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class Constants
{
    public const NAME = 'MailChimp';

    public const WEBHOOK_SUBSCRIBE   = 'subscribe';
    public const WEBHOOK_UNSUBSCRIBE = 'unsubscribe';
    public const WEBHOOK_PROFILE     = 'profile';
    public const WEBHOOK_CLEANED     = 'cleaned';
    public const WEBHOOK_UPEMAIL     = 'upemail';
    public const WEBHOOK_CAMPAIGN    = 'campaign';

    public const SOURCE_USER  = 'user';
    public const SOURCE_ADMIN = 'admin';
    public const SOURCE_API   = 'api';


    /**
     * Returns all the webhooks.
     *
     * @return array
     */
    public static function getWebhooks(): array
    {
        return [
            self::WEBHOOK_SUBSCRIBE,
            self::WEBHOOK_UNSUBSCRIBE,
            self::WEBHOOK_PROFILE,
            self::WEBHOOK_CLEANED,
            self::WEBHOOK_UPEMAIL,
            self::WEBHOOK_CAMPAIGN,
        ];
    }

    /**
     * Returns all the webhook sources.
     *
     * @return array
     */
    public static function getSources(): array
    {
        return [
            self::SOURCE_USER,
            self::SOURCE_ADMIN,
            self::SOURCE_API,
        ];
    }

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
