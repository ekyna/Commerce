<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Support\Event\TicketEvents;
use Ekyna\Component\Commerce\Support\EventListener\TicketEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TicketEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketEventSubscriber extends TicketEventListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            TicketEvents::INSERT         => ['onInsert', 0],
            TicketEvents::UPDATE         => ['onUpdate', 0],
            TicketEvents::CONTENT_CHANGE => ['onContentChange', 0],
        ];
    }
}
