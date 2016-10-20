<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Quote\Event\QuotePaymentEvents;
use Ekyna\Component\Commerce\Quote\EventListener\QuotePaymentListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class QuotePaymentEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class QuotePaymentEventSubscriber extends QuotePaymentListener implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            QuotePaymentEvents::INSERT     => ['onInsert', 0],
            QuotePaymentEvents::UPDATE     => ['onUpdate', 0],
            QuotePaymentEvents::DELETE     => ['onDelete', 0],
            QuotePaymentEvents::PRE_UPDATE => ['onPreUpdate', 0],
            QuotePaymentEvents::PRE_DELETE => ['onPreDelete', 0],
        ];
    }
}
