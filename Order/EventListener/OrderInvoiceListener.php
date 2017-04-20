<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\EventListener\AbstractInvoiceListener;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderInvoiceListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderInvoiceListener extends AbstractInvoiceListener
{
    protected function preventForbiddenChange(InvoiceInterface $invoice): void
    {
        parent::preventForbiddenChange($invoice);

        if (!$invoice instanceof OrderInvoiceInterface) {
            throw new Exception\UnexpectedTypeException($invoice, OrderInvoiceInterface::class);
        }

        if ($this->persistenceHelper->isChanged($invoice, 'currency')) {
            [$old, $new] = $this->persistenceHelper->getChangeSet($invoice, 'currency');
            if ($old != $new) {
                throw new Exception\RuntimeException('Changing the invoice\'s currency is not yet supported.');
            }
        }

        if ($this->persistenceHelper->isChanged($invoice, 'order')) {
            [$old, $new] = $this->persistenceHelper->getChangeSet($invoice, 'order');
            if ($old != $new) {
                throw new Exception\RuntimeException('Changing the invoice\'s order is not yet supported.');
            }
        }
    }

    protected function scheduleSaleContentChangeEvent(SaleInterface $sale): void
    {
        $this->persistenceHelper->scheduleEvent($sale, OrderEvents::CONTENT_CHANGE);
    }

    protected function getInvoiceFromEvent(ResourceEventInterface $event): InvoiceInterface
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderInvoiceInterface) {
            throw new Exception\UnexpectedTypeException($resource, OrderInvoiceInterface::class);
        }

        return $resource;
    }

    protected function getSalePropertyPath(): string
    {
        return 'order';
    }
}
