<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartAddressEvents;
use Ekyna\Component\Commerce\Cart\EventListener\CartAddressListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CartAddressEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAddressEventSubscriber extends CartAddressListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CartAddressEvents::UPDATE => ['onUpdate', 0],
        ];
    }
}
