<?php

namespace Ekyna\Component\Commerce\Support\Event;

/**
 * Class TicketEvents
 * @package Ekyna\Component\Commerce\Support\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TicketEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.ticket.insert';
    const UPDATE         = 'ekyna_commerce.ticket.update';
    const DELETE         = 'ekyna_commerce.ticket.delete';

    // Domain
    const CONTENT_CHANGE = 'ekyna_commerce.ticket.content_change';

    const INITIALIZE     = 'ekyna_commerce.ticket.initialize';

    const PRE_CREATE     = 'ekyna_commerce.ticket.pre_create';
    const POST_CREATE    = 'ekyna_commerce.ticket.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.ticket.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.ticket.post_update';

    const PRE_DELETE     = 'ekyna_commerce.ticket.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.ticket.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
