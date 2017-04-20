<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Event;

/**
 * Class TicketMessageEvents
 * @package Ekyna\Component\Commerce\Support\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TicketMessageEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.ticket_message.insert';
    public const UPDATE         = 'ekyna_commerce.ticket_message.update';
    public const DELETE         = 'ekyna_commerce.ticket_message.delete';

    // Domain
    public const PRE_CREATE     = 'ekyna_commerce.ticket_message.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.ticket_message.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.ticket_message.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.ticket_message.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.ticket_message.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.ticket_message.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
