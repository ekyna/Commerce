<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Quote\Event\QuoteAddressEvents;
use Ekyna\Component\Commerce\Quote\EventListener\QuoteAddressListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QuoteAddressEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAddressEventSubscriber extends QuoteAddressListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            QuoteAddressEvents::UPDATE => ['onUpdate', 0],
        ];
    }
}
