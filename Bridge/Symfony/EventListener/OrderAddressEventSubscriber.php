<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderAddressEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderAddressListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderAddressEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderAddressEventSubscriber extends OrderAddressListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderAddressEvents::UPDATE => ['onUpdate', 0],
        ];
    }
}
