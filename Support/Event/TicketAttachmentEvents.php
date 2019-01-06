<?php

namespace Ekyna\Component\Commerce\Support\Event;

/**
 * Class TicketAttachmentEvents
 * @package Ekyna\Component\Commerce\Support\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TicketAttachmentEvents
{
    // Persistence
    const INSERT         = 'ekyna_commerce.ticket_attachment.insert';
    const UPDATE         = 'ekyna_commerce.ticket_attachment.update';
    const DELETE         = 'ekyna_commerce.ticket_attachment.delete';

    // Domain
    const PRE_CREATE     = 'ekyna_commerce.ticket_attachment.pre_create';
    const POST_CREATE    = 'ekyna_commerce.ticket_attachment.post_create';

    const PRE_UPDATE     = 'ekyna_commerce.ticket_attachment.pre_update';
    const POST_UPDATE    = 'ekyna_commerce.ticket_attachment.post_update';

    const PRE_DELETE     = 'ekyna_commerce.ticket_attachment.pre_delete';
    const POST_DELETE    = 'ekyna_commerce.ticket_attachment.post_delete';


    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
