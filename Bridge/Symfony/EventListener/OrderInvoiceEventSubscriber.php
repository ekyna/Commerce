<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderInvoiceEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderInvoiceListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderInvoiceEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceEventSubscriber extends OrderInvoiceListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderInvoiceEvents::INSERT         => ['onInsert', 0],
            OrderInvoiceEvents::UPDATE         => ['onUpdate', 0],
            OrderInvoiceEvents::DELETE         => ['onDelete', 0],
            OrderInvoiceEvents::CONTENT_CHANGE => ['onContentChange', 0],
        ];
    }
}
