<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderInvoiceItemEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderInvoiceItemListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderInvoiceItemEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceItemEventSubscriber extends OrderInvoiceItemListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OrderInvoiceItemEvents::INSERT => ['onInsert', 0],
            OrderInvoiceItemEvents::UPDATE => ['onUpdate', 0],
            OrderInvoiceItemEvents::DELETE => ['onDelete', 0],
        ];
    }
}
