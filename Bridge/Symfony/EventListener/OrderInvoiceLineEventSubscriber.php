<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Bridge\Symfony\EventListener;

use Ekyna\Component\Commerce\Order\Event\OrderInvoiceLineEvents;
use Ekyna\Component\Commerce\Order\EventListener\OrderInvoiceLineListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class OrderInvoiceLineEventSubscriber
 * @package Ekyna\Component\Commerce\Bridge\Symfony\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceLineEventSubscriber extends OrderInvoiceLineListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            OrderInvoiceLineEvents::INSERT => ['onInsert', 0],
            OrderInvoiceLineEvents::UPDATE => ['onUpdate', 0],
            OrderInvoiceLineEvents::DELETE => ['onDelete', 0],
        ];
    }
}
