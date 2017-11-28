<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderItemAdjustmentEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderItemAdjustmentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderItemAdjustmentEventSubscriber
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderItemAdjustmentEventSubscriber extends OrderItemAdjustmentListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderItemAdjustmentEvents::INSERT     => ['onInsert', 0],
            OrderItemAdjustmentEvents::UPDATE     => ['onUpdate', 0],
            OrderItemAdjustmentEvents::DELETE     => ['onDelete', 0],
            OrderItemAdjustmentEvents::PRE_UPDATE => ['onPreUpdate', 0],
            OrderItemAdjustmentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
