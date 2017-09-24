<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Shipment\Event\ShipmentMethodEvents;
use Ekyna\Component\Commerce\Shipment\EventListener\ShipmentMethodListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ShipmentMethodEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentMethodEventSubscriber extends ShipmentMethodListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            ShipmentMethodEvents::INSERT => ['onInsert', 0],
        ];
    }
}
