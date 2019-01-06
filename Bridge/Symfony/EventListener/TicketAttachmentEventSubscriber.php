<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Support\Event\TicketAttachmentEvents;
use Ekyna\Component\Commerce\Support\EventListener\TicketAttachmentEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TicketAttachmentEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketAttachmentEventSubscriber extends TicketAttachmentEventListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            TicketAttachmentEvents::INSERT => ['onInsert', 0],
            TicketAttachmentEvents::UPDATE => ['onUpdate', 0],
            TicketAttachmentEvents::DELETE => ['onDelete', 0],
        ];
    }
}
