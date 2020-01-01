<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Customer\Event\CustomerEvents;
use Ekyna\Component\Commerce\Customer\EventListener\LoyaltyListener;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class LoyaltyEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoyaltyEventSubscriber extends LoyaltyListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            CustomerEvents::BIRTHDAY             => 'onBirthday',
            CustomerEvents::NEWSLETTER_SUBSCRIBE => 'onNewsletterSubscribe',
            OrderEvents::COMPLETED               => 'onOrderCompleted',
        ];
    }
}
