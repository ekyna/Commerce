<?php

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractInvoiceLineListener
 * @package Ekyna\Component\Commerce\Invoice\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceLineListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $line = $this->getInvoiceLineFromEvent($event);

        $this->scheduleInvoiceContentChangeEvent($line->getInvoice());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $line = $this->getInvoiceLineFromEvent($event);

        $this->preventForbiddenChange($line);

        // If invoice item quantity has changed
        if ($this->persistenceHelper->isChanged($line, 'quantity')) {
            $this->scheduleInvoiceContentChangeEvent($line->getInvoice());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getInvoiceLineFromEvent($event);
        $invoice = $item->getInvoice();

        // TODO get invoice from change set if null ?

        $this->scheduleInvoiceContentChangeEvent($invoice);
    }

    /**
     * Prevents some of the invoice line fields from changing.
     *
     * @param Model\InvoiceLineInterface $line
     */
    protected function preventForbiddenChange(Model\InvoiceLineInterface $line)
    {
        if ($this->persistenceHelper->isChanged($line, 'type')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($line, 'type');
            if ($old !== $new) {
                throw new Exception\RuntimeException("Changing the invoice line's type is not supported.");
            }
        }
    }

    /**
     * Schedules the invoice content change event.
     *
     * @param Model\InvoiceInterface $invoice
     */
    abstract protected function scheduleInvoiceContentChangeEvent(Model\InvoiceInterface $invoice);

    /**
     * Returns the invoice line from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\InvoiceLineInterface
     * @throws Exception\InvalidArgumentException
     */
    abstract protected function getInvoiceLineFromEvent(ResourceEventInterface $event);
}
