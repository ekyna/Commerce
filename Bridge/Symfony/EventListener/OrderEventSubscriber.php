<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderEventSubscriber extends OrderListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OrderEvents::INSERT         => ['onInsert', 0],
            OrderEvents::UPDATE         => ['onUpdate', 0],
            OrderEvents::CONTENT_CHANGE => ['onContentChange', 0],
            OrderEvents::ADDRESS_CHANGE => ['onAddressChange', 0],
            OrderEvents::STATE_CHANGE   => ['onStateChange', 0],
            OrderEvents::PREPARE        => ['onPrepare', 0],
            OrderEvents::PRE_CREATE     => ['onPreCreate', 0],
            OrderEvents::PRE_UPDATE     => ['onPreUpdate', 0],
            OrderEvents::PRE_DELETE     => ['onPreDelete', 0],
        ];
    }
}
