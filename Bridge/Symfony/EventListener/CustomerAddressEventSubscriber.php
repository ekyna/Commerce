<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Customer\Event\CustomerAddressEvents;
use Ekyna\Component\Commerce\Customer\EventListener\CustomerAddressListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerAddressEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerAddressEventSubscriber extends CustomerAddressListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerAddressEvents::INSERT     => ['onInsert', 0],
            CustomerAddressEvents::UPDATE     => ['onUpdate', 0],
            CustomerAddressEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
