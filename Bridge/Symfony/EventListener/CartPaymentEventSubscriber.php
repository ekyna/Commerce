<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartPaymentEvents;
use Ekyna\Component\Commerce\Cart\EventListener\CartPaymentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CartPaymentEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartPaymentEventSubscriber extends CartPaymentListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CartPaymentEvents::INSERT     => ['onInsert', 0],
            CartPaymentEvents::UPDATE     => ['onUpdate', 0],
            CartPaymentEvents::DELETE     => ['onDelete', 0],
            CartPaymentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
