<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Support\Event\TicketMessageEvents;
use Ekyna\Component\Commerce\Support\EventListener\TicketMessageEventListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TicketMessageEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TicketMessageEventSubscriber extends TicketMessageEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TicketMessageEvents::INSERT     => ['onInsert', 0],
            TicketMessageEvents::UPDATE     => ['onUpdate', 0],
            TicketMessageEvents::DELETE     => ['onDelete', 0],
            TicketMessageEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
