<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Quote\Event\QuoteItemAdjustmentEvents;
use Ekyna\Component\Commerce\Quote\EventListener\QuoteItemAdjustmentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QuoteItemAdjustmentEventSubscriber
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemAdjustmentEventSubscriber extends QuoteItemAdjustmentListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            QuoteItemAdjustmentEvents::INSERT     => ['onInsert', 0],
            QuoteItemAdjustmentEvents::UPDATE     => ['onUpdate', 0],
            QuoteItemAdjustmentEvents::DELETE     => ['onDelete', 0],
            QuoteItemAdjustmentEvents::PRE_UPDATE => ['onPreUpdate', 0],
            QuoteItemAdjustmentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
