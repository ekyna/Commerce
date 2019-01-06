<?php

namespace Ekyna\Component\Commerce\Support\Event;

/**
 * Class TicketMessageEvents
 * @package Ekyna\Component\Commerce\Support\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TicketMessageEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.ticket_message.insert';
    const UPDATE         = 'ekyna_commerce.ticket_message.update';
    const DELETE         = 'ekyna_commerce.ticket_message.delete';

    // Domain
    const INITIALIZE     = 'ekyna_commerce.ticket_message.initialize';

    const PRE_CREATE     = 'ekyna_commerce.ticket_message.pre_create';
    const POST_CREATE    = 'ekyna_commerce.ticket_message.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.ticket_message.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.ticket_message.post_update';

    const PRE_DELETE     = 'ekyna_commerce.ticket_message.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.ticket_message.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
