<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartEvents;
use Ekyna\Component\Commerce\Cart\EventListener\CartListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CartEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartEventSubscriber extends CartListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CartEvents::INSERT         => ['onInsert', 0],
            CartEvents::UPDATE         => ['onUpdate', 0],
            CartEvents::CONTENT_CHANGE => ['onContentChange', 0],
            CartEvents::ADDRESS_CHANGE => ['onAddressChange', 0],
            CartEvents::STATE_CHANGE   => ['onStateChange', 0],
            CartEvents::INITIALIZE     => ['onInitialize', 0],
            CartEvents::PRE_CREATE     => ['onPreCreate', 0],
            CartEvents::PRE_DELETE     => ['onPreDelete', 0],
        ];
    }
}
