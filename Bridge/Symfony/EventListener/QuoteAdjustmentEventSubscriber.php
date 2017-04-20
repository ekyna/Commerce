<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Quote\Event\QuoteAdjustmentEvents;
use Ekyna\Component\Commerce\Quote\EventListener\QuoteAdjustmentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QuoteAdjustmentEventSubscriber
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteAdjustmentEventSubscriber extends QuoteAdjustmentListener implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            QuoteAdjustmentEvents::INSERT     => ['onInsert', 0],
            QuoteAdjustmentEvents::UPDATE     => ['onUpdate', 0],
            QuoteAdjustmentEvents::DELETE     => ['onDelete', 0],
            QuoteAdjustmentEvents::PRE_UPDATE => ['onPreUpdate', 0],
            QuoteAdjustmentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
