<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderCreditItemEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderCreditItemListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderCreditEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderCreditItemEventSubscriber extends OrderCreditItemListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderCreditItemEvents::INSERT => ['onInsert', 0],
            OrderCreditItemEvents::UPDATE => ['onUpdate', 0],
            OrderCreditItemEvents::DELETE => ['onDelete', 0],
        ];
    }
}
