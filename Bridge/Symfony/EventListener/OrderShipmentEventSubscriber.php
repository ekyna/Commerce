<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderShipmentEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderShipmentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderShipmentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentEventSubscriber extends OrderShipmentListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderShipmentEvents::INSERT     => ['onInsert', 0],
            OrderShipmentEvents::UPDATE     => ['onUpdate', 0],
            OrderShipmentEvents::DELETE     => ['onDelete', 0],
            OrderShipmentEvents::PRE_CREATE => ['onPreCreate', 0],
            OrderShipmentEvents::PRE_UPDATE => ['onPreUpdate', 0],
            OrderShipmentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
