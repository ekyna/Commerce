<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderShipmentItemEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderShipmentItemListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderShipmentEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItemEventSubscriber extends OrderShipmentItemListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderShipmentItemEvents::INSERT => ['onInsert', 0],
            OrderShipmentItemEvents::UPDATE => ['onUpdate', 0],
            OrderShipmentItemEvents::DELETE => ['onDelete', 0],
        ];
    }
}
