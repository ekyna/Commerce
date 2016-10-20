<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderShipmentItemEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderShipmentItemListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderShipmentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItemEventSubscriber extends OrderShipmentItemListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderShipmentItemEvents::INSERT     => ['onInsert', 0],
            OrderShipmentItemEvents::UPDATE     => ['onUpdate', 0],
            OrderShipmentItemEvents::DELETE     => ['onDelete', 0],
            OrderShipmentItemEvents::PRE_CREATE => ['onPreCreate', 0],
            OrderShipmentItemEvents::PRE_UPDATE => ['onPreUpdate', 0],
            OrderShipmentItemEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
