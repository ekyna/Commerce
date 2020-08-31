<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Newsletter\Event\SubscriptionEvents;
use Ekyna\Component\Commerce\Newsletter\EventListener\SubscriptionListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class SubscriptionEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SubscriptionEventSubscriber extends SubscriptionListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SubscriptionEvents::INSERT => ['onInsert', 0],
            SubscriptionEvents::UPDATE => ['onUpdate', 0],
            SubscriptionEvents::DELETE => ['onDelete', 0],
        ];
    }
}
