<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Quote\Event\QuoteItemEvents;
use Ekyna\Component\Commerce\Quote\EventListener\QuoteItemListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QuoteItemEventSubscriber
 * @package Ekyna\Component\Commerce\Quote\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuoteItemEventSubscriber extends QuoteItemListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            QuoteItemEvents::INSERT     => ['onInsert', 0],
            QuoteItemEvents::UPDATE     => ['onUpdate', 0],
            QuoteItemEvents::DELETE     => ['onDelete', 0],
            QuoteItemEvents::PRE_UPDATE => ['onPreUpdate', 0],
            QuoteItemEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
