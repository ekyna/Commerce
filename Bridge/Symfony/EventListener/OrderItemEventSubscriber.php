<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderItemEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderItemListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderItemEventSubscriber
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemEventSubscriber extends OrderItemListener  implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderItemEvents::INSERT     => ['onInsert', 0],
            OrderItemEvents::UPDATE     => ['onUpdate', 0],
            OrderItemEvents::DELETE     => ['onDelete', 0],
            OrderItemEvents::PRE_CREATE => ['onPreCreate', 0],
            OrderItemEvents::PRE_UPDATE => ['onPreUpdate', 0],
            OrderItemEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
