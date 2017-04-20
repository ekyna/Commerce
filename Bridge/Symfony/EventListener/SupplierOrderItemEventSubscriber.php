<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderItemEvents;
use Ekyna\Component\Commerce\Supplier\EventListener\SupplierOrderItemListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SupplierOrderItemEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderItemEventSubscriber extends SupplierOrderItemListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SupplierOrderItemEvents::INSERT     => ['onInsert', 0],
            SupplierOrderItemEvents::UPDATE     => ['onUpdate', 0],
            SupplierOrderItemEvents::DELETE     => ['onDelete', 0],
            SupplierOrderItemEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
