<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Order\Event\OrderInvoiceEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceLineInterface;
use Ekyna\Component\Commerce\Invoice\EventListener\AbstractInvoiceLineListener;
use Ekyna\Component\Commerce\Invoice\Model as Invoice;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderInvoiceLineListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceLineListener extends AbstractInvoiceLineListener
{
    protected function preventForbiddenChange(Invoice\InvoiceLineInterface $line): void
    {
        parent::preventForbiddenChange($line);

        if (!$line instanceof OrderInvoiceLineInterface) {
            throw new Exception\UnexpectedTypeException($line, OrderInvoiceLineInterface::class);
        }

        if ($this->persistenceHelper->isChanged($line, 'orderItem')) {
            [$old, $new] = $this->persistenceHelper->getChangeSet($line, 'orderItem');
            if ($old !== $new) {
                throw new Exception\RuntimeException("Changing the invoice line's sale item is not yet supported.");
            }
        }
    }

    protected function scheduleInvoiceContentChangeEvent(Invoice\InvoiceInterface $invoice): void
    {
        $this->persistenceHelper->scheduleEvent($invoice, OrderInvoiceEvents::CONTENT_CHANGE);
    }

    protected function getInvoiceLineFromEvent(ResourceEventInterface $event): Invoice\InvoiceLineInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInvoiceLineInterface) {
            throw new Exception\UnexpectedTypeException($resource, OrderInvoiceLineInterface::class);
        }

        return $resource;
    }
}
