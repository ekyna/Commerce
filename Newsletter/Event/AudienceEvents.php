<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Newsletter\Event;

/**
 * Class AudienceEvents
 * @package Ekyna\Component\Commerce\Newsletter\Event
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
final class AudienceEvents
{
    // Persistence
    public const INSERT      = 'ekyna_commerce.audience.insert';
    public const UPDATE      = 'ekyna_commerce.audience.update';
    public const DELETE      = 'ekyna_commerce.audience.delete';

    // Domain
    public const PRE_CREATE  = 'ekyna_commerce.audience.pre_create';
    public const POST_CREATE = 'ekyna_commerce.audience.post_create';

    public const PRE_UPDATE  = 'ekyna_commerce.audience.pre_update';
    public const POST_UPDATE = 'ekyna_commerce.audience.post_update';

    public const PRE_DELETE  = 'ekyna_commerce.audience.pre_delete';
    public const POST_DELETE = 'ekyna_commerce.audience.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
