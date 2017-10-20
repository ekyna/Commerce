<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Quote\Event\QuoteEvents;
use Ekyna\Component\Commerce\Quote\EventListener\QuoteListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QuoteEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteEventSubscriber extends QuoteListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            QuoteEvents::INSERT         => ['onInsert', 0],
            QuoteEvents::UPDATE         => ['onUpdate', 0],
            QuoteEvents::CONTENT_CHANGE => ['onContentChange', 0],
            QuoteEvents::ADDRESS_CHANGE => ['onAddressChange', 0],
            QuoteEvents::STATE_CHANGE   => ['onStateChange', 0],
            QuoteEvents::PRE_CREATE     => ['onPreCreate', 0],
            QuoteEvents::PRE_DELETE     => ['onPreDelete', 0],
        ];
    }
}
