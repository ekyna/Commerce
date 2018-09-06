<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Cart\Event\CartItemAdjustmentEvents;
use Ekyna\Component\Commerce\Cart\EventListener\CartItemAdjustmentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CartItemAdjustmentEventSubscriber
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartItemAdjustmentEventSubscriber extends CartItemAdjustmentListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CartItemAdjustmentEvents::INSERT     => ['onInsert', 0],
            CartItemAdjustmentEvents::UPDATE     => ['onUpdate', 0],
            CartItemAdjustmentEvents::DELETE     => ['onDelete', 0],
            CartItemAdjustmentEvents::PRE_UPDATE => ['onPreUpdate', 0],
            CartItemAdjustmentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
