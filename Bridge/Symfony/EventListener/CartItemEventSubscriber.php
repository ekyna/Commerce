<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Cart\EventListener\CartItemListener;
use Ekyna\Component\Commerce\Cart\Event\CartItemEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CartItemEventSubscriber
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemEventSubscriber extends CartItemListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CartItemEvents::INSERT     => ['onInsert', 0],
            CartItemEvents::UPDATE     => ['onUpdate', 0],
            CartItemEvents::DELETE     => ['onDelete', 0],
            CartItemEvents::PRE_UPDATE => ['onPreUpdate', 0],
            CartItemEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
