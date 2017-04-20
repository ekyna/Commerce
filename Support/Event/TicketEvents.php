<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Event;

/**
 * Class TicketEvents
 * @package Ekyna\Component\Commerce\Support\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TicketEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.ticket.insert';
    public const UPDATE         = 'ekyna_commerce.ticket.update';
    public const DELETE         = 'ekyna_commerce.ticket.delete';

    // Domain
    public const CONTENT_CHANGE = 'ekyna_commerce.ticket.content_change';

    public const PRE_CREATE     = 'ekyna_commerce.ticket.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.ticket.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.ticket.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.ticket.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.ticket.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.ticket.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
