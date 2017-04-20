<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\EventListener\CustomerListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerEventSubscriber extends CustomerListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerEvents::INSERT        => ['onInsert', 0],
            CustomerEvents::UPDATE        => ['onUpdate', 0],
            CustomerEvents::PARENT_CHANGE => ['onParentChange', 0],
        ];
    }
}
