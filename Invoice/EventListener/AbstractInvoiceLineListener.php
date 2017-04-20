<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Common\Model\LockingHelperAwareTrait;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractInvoiceLineListener
 * @package Ekyna\Component\Commerce\Invoice\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceLineListener
{
    use LockingHelperAwareTrait;

    protected PersistenceHelperInterface $persistenceHelper;
    protected StockUnitAssignerInterface $stockUnitAssigner;

    public function setPersistenceHelper(PersistenceHelperInterface $helper): void
    {
        $this->persistenceHelper = $helper;
    }

    public function setStockUnitAssigner(StockUnitAssignerInterface $stockUnitAssigner): void
    {
        $this->stockUnitAssigner = $stockUnitAssigner;
    }

    public function onInsert(ResourceEventInterface $event): void
    {
        $line = $this->getInvoiceLineFromEvent($event);

        $this->stockUnitAssigner->assignInvoiceLine($line);

        $this->scheduleInvoiceContentChangeEvent($line->getInvoice());
    }

    public function onUpdate(ResourceEventInterface $event): void
    {
        $line = $this->getInvoiceLineFromEvent($event);

        $this->preventForbiddenChange($line);

        $this->stockUnitAssigner->applyInvoiceLine($line);

        // If invoice item quantity has changed
        if ($this->persistenceHelper->isChanged($line, 'quantity')) {
            $this->scheduleInvoiceContentChangeEvent($line->getInvoice());
        }
    }

    public function onDelete(ResourceEventInterface $event): void
    {
        $line = $this->getInvoiceLineFromEvent($event);

        // Get invoice from change set if null
        if (null === $invoice = $line->getInvoice()) {
            $invoice = $this->persistenceHelper->getChangeSet($line, 'invoice')[0];
        }

        if ($this->lockingHelper->isLocked($invoice)) {
            throw new Exception\IllegalOperationException(
                'This invoice is locked.'
            );
        }

        $this->stockUnitAssigner->detachInvoiceLine($line);

        $this->scheduleInvoiceContentChangeEvent($invoice);
    }

    /**
     * Prevents some invoice line fields from changing.
     */
    protected function preventForbiddenChange(Model\InvoiceLineInterface $line): void
    {
        if (empty($cs = $this->persistenceHelper->getChangeSet($line))) {
            return;
        }

        if ($this->lockingHelper->isLocked($line->getInvoice())) {
            throw new Exception\IllegalOperationException(
                'This invoice is locked.'
            );
        }

        if (!isset($cs['type'])) {
            return;
        }

        [$old, $new] = $cs['type'];
        if ($old !== $new) {
            throw new Exception\IllegalOperationException(
                'Changing the invoice line\'s type is not supported.'
            );
        }
    }

    /**
     * Schedules the invoice content change event.
     */
    abstract protected function scheduleInvoiceContentChangeEvent(Model\InvoiceInterface $invoice): void;

    /**
     * Returns the invoice line from the event.
     */
    abstract protected function getInvoiceLineFromEvent(ResourceEventInterface $event): Model\InvoiceLineInterface;
}
