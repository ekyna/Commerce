<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Supplier\Event\SupplierDeliveryItemEvents;
use Ekyna\Component\Commerce\Supplier\EventListener\SupplierDeliveryItemListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SupplierOrderEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryItemEventSubscriber extends SupplierDeliveryItemListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            SupplierDeliveryItemEvents::INSERT     => ['onInsert', 0],
            SupplierDeliveryItemEvents::UPDATE     => ['onUpdate', 0],
            SupplierDeliveryItemEvents::DELETE     => ['onDelete', 0],
            SupplierDeliveryItemEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
