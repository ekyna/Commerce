<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderEventSubscriber extends OrderListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderEvents::INSERT         => ['onInsert', 0],
            OrderEvents::UPDATE         => ['onUpdate', 0],
            OrderEvents::CONTENT_CHANGE => ['onContentChange', 0],
            OrderEvents::PRE_DELETE     => ['onPreDelete', 0],
        ];
    }
}
