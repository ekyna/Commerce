<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderCreditEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderCreditListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderCreditEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderCreditEventSubscriber extends OrderCreditListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderCreditEvents::INSERT         => ['onInsert', 0],
            OrderCreditEvents::UPDATE         => ['onUpdate', 0],
            OrderCreditEvents::DELETE         => ['onDelete', 0],
            OrderCreditEvents::CONTENT_CHANGE => ['onContentChange', 0],
        ];
    }
}
