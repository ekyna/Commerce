<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Cart\EventListener\CartAdjustmentListener;
use Ekyna\Component\Commerce\Cart\Event\CartAdjustmentEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CartAdjustmentEventSubscriber
 * @package Ekyna\Component\Commerce\Cart\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CartAdjustmentEventSubscriber extends CartAdjustmentListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            CartAdjustmentEvents::INSERT     => ['onInsert', 0],
            CartAdjustmentEvents::UPDATE     => ['onUpdate', 0],
            CartAdjustmentEvents::DELETE     => ['onDelete', 0],
            CartAdjustmentEvents::PRE_UPDATE => ['onPreUpdate', 0],
            CartAdjustmentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
