<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Newsletter\Event\MemberEvents;
use Ekyna\Component\Commerce\Newsletter\EventListener\MemberListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class MemberEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MemberEventSubscriber extends MemberListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            MemberEvents::PRE_CREATE          => ['onPreCreate', 0],
            MemberEvents::INSERT              => ['onInsert', 0],
            MemberEvents::UPDATE              => ['onUpdate', 0],
            MemberEvents::SUBSCRIPTION_CHANGE => ['onSubscriptionChange', 0],
        ];
    }
}
