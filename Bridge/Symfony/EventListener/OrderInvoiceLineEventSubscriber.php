<?php

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderInvoiceLineEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderInvoiceLineListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderInvoiceLineEventSubscriber
 * @package Ekyna\Bundle\CommerceBundle\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceLineEventSubscriber extends OrderInvoiceLineListener implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            OrderInvoiceLineEvents::INSERT => ['onInsert', 0],
            OrderInvoiceLineEvents::UPDATE => ['onUpdate', 0],
            OrderInvoiceLineEvents::DELETE => ['onDelete', 0],
        ];
    }
}
