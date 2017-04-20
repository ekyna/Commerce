<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\EventListener\AbstractInvoiceItemListener;
use Ekyna\Component\Commerce\Invoice\Model;
use Ekyna\Component\Commerce\Order\Event\OrderInvoiceEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceItemInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderInvoiceItemListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceItemListener extends AbstractInvoiceItemListener
{
    protected function scheduleInvoiceContentChangeEvent(Model\InvoiceInterface $invoice): void
    {
        $this->persistenceHelper->scheduleEvent($invoice, OrderInvoiceEvents::CONTENT_CHANGE);
    }

    protected function getInvoiceItemFromEvent(ResourceEventInterface $event): Model\InvoiceItemInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInvoiceItemInterface) {
            throw new Exception\UnexpectedTypeException($resource, OrderInvoiceItemInterface::class);
        }

        return $resource;
    }
}
