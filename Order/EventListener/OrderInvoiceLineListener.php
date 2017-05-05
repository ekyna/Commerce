<?php

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
    /**
     * @inheritDoc
     */
    protected function preventForbiddenChange(Invoice\InvoiceLineInterface $line)
    {
        parent::preventForbiddenChange($line);

        if (!$line instanceof OrderInvoiceLineInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderInvoiceLineInterface");
        }

        if ($this->persistenceHelper->isChanged($line, 'orderItem')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($line, 'orderItem');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the invoice line's sale item is not yet supported.");
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function scheduleInvoiceContentChangeEvent(Invoice\InvoiceInterface $invoice)
    {
        $this->persistenceHelper->scheduleEvent(OrderInvoiceEvents::CONTENT_CHANGE, $invoice);
    }

    /**
     * @inheritdoc
     */
    protected function getInvoiceLineFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInvoiceLineInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderInvoiceLineInterface");
        }

        return $resource;
    }
}
