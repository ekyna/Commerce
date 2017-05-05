<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Commerce\Invoice\EventListener\AbstractInvoiceListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderInvoiceListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceListener extends AbstractInvoiceListener
{
    /**
     * @inheritDoc
     */
    protected function preventForbiddenChange(InvoiceInterface $invoice)
    {
        parent::preventForbiddenChange($invoice);

        if (!$invoice instanceof OrderInvoiceInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderInvoiceInterface");
        }

        if ($this->persistenceHelper->isChanged($invoice, 'currency')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($invoice, 'currency');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the invoice's currency is not yet supported.");
            }
        }

        if ($this->persistenceHelper->isChanged($invoice, 'order')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($invoice, 'order');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the invoice's order is not yet supported.");
            }
        }
    }

    /**
     * @inheritdoc
     */
    protected function scheduleSaleContentChangeEvent(SaleInterface $sale)
    {
        $this->persistenceHelper->scheduleEvent(OrderEvents::CONTENT_CHANGE, $sale);
    }

    /**
     * @inheritdoc
     */
    protected function getInvoiceFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInvoiceInterface) {
            throw new Exception\InvalidArgumentException("Expected instance of OrderInvoiceInterface");
        }

        return $resource;
    }
}
