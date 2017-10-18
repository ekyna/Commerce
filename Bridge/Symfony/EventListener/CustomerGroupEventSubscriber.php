<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Customer\Event\CustomerGroupEvents;
use Ekyna\Component\Commerce\Customer\EventListener\CustomerGroupListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class CustomerGroupEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CustomerGroupEventSubscriber extends CustomerGroupListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerGroupEvents::INSERT     => ['onInsert', 0],
            CustomerGroupEvents::UPDATE     => ['onUpdate', 0],
            CustomerGroupEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
