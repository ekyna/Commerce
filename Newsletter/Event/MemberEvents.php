<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Event;

/**
 * Class MemberEvents
 * @package Ekyna\Component\Commerce\Newsletter\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class MemberEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.member.insert';
    public const UPDATE      = 'ekyna_commerce.member.update';
    public const DELETE      = 'ekyna_commerce.member.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.member.pre_create';
    public const POST_CREATE = 'ekyna_commerce.member.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.member.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.member.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.member.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.member.post_delete';

    public const SUBSCRIPTION_CHANGE = 'ekyna_commerce.member.subscription_change';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
