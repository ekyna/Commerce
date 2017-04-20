<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Supplier\Event\SupplierDeliveryEvents;
use Ekyna\Component\Commerce\Supplier\EventListener\SupplierDeliveryListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SupplierDeliveryEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierDeliveryEventSubscriber extends SupplierDeliveryListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SupplierDeliveryEvents::DELETE     => ['onDelete', 0],
            SupplierDeliveryEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
