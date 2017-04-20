<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Support\Event;

/**
 * Class TicketAttachmentEvents
 * @package Ekyna\Component\Commerce\Support\Event
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class TicketAttachmentEvents
{
    // Persistence
    public const INSERT         = 'ekyna_commerce.ticket_attachment.insert';
    public const UPDATE         = 'ekyna_commerce.ticket_attachment.update';
    public const DELETE         = 'ekyna_commerce.ticket_attachment.delete';

    // Domain
    public const PRE_CREATE     = 'ekyna_commerce.ticket_attachment.pre_create';
    public const POST_CREATE    = 'ekyna_commerce.ticket_attachment.post_create';

    public const PRE_UPDATE     = 'ekyna_commerce.ticket_attachment.pre_update';
    public const POST_UPDATE    = 'ekyna_commerce.ticket_attachment.post_update';

    public const PRE_DELETE     = 'ekyna_commerce.ticket_attachment.pre_delete';
    public const POST_DELETE    = 'ekyna_commerce.ticket_attachment.post_delete';

    /**
     * Disabled constructor.
     */
    private function __construct()
    {
    }
}
