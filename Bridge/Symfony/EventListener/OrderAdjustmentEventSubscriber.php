<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderAdjustmentEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderAdjustmentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderAdjustmentEventSubscriber
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAdjustmentEventSubscriber extends OrderAdjustmentListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderAdjustmentEvents::INSERT     => ['onInsert', 0],
            OrderAdjustmentEvents::UPDATE     => ['onUpdate', 0],
            OrderAdjustmentEvents::DELETE     => ['onDelete', 0],
            OrderAdjustmentEvents::PRE_UPDATE => ['onPreUpdate', 0],
            OrderAdjustmentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
