<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Supplier\Event\SupplierOrderEvents;
use Ekyna\Component\Commerce\Supplier\EventListener\SupplierOrderListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SupplierOrderEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderEventSubscriber extends SupplierOrderListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            SupplierOrderEvents::INSERT     => ['onInsert', 0],
            SupplierOrderEvents::UPDATE     => ['onUpdate', 0],
//            SupplierOrderEvents::DELETE     => ['onDelete', 0],
            SupplierOrderEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
