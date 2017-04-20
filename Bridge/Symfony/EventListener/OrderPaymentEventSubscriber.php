<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderPaymentEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderPaymentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderPaymentEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPaymentEventSubscriber extends OrderPaymentListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            OrderPaymentEvents::INSERT     => ['onInsert', 0],
            OrderPaymentEvents::UPDATE     => ['onUpdate', 0],
            OrderPaymentEvents::DELETE     => ['onDelete', 0],
            OrderPaymentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
