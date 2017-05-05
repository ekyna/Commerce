<?php

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
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
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitAssignerInterface
     */
    protected $stockUnitAssigner;


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
     * Sets the stock assigner.
     *
     * @param StockUnitAssignerInterface $stockUnitAssigner
     */
    public function setStockUnitAssigner(StockUnitAssignerInterface $stockUnitAssigner)
    {
        $this->stockUnitAssigner = $stockUnitAssigner;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $line = $this->getInvoiceLineFromEvent($event);

        if ($this->synchronizeSubjectIdentity($line)) {
            $this->persistenceHelper->persistAndRecompute($line, false);
        }

        $invoice = $line->getInvoice();
        // Assign invoice item to stock units
        if ($invoice->getType() === Model\InvoiceTypes::TYPE_CREDIT) {
            $this->stockUnitAssigner->assignInvoiceLine($line);
        }

        $this->scheduleInvoiceContentChangeEvent($invoice);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $line = $this->getInvoiceLineFromEvent($event);
        $invoice = $line->getInvoice();

        $this->preventForbiddenChange($line);

        if ($this->synchronizeSubjectIdentity($line)) {
            $this->persistenceHelper->persistAndRecompute($line, false);
        }

        $sale = $invoice->getSale();

        $doApply = false;
        if ($invoice->getType() === Model\InvoiceTypes::TYPE_CREDIT) {
            $doApply = true;
            if ($this->persistenceHelper->isChanged($sale, 'state')) {
                $stateCs = $this->persistenceHelper->getChangeSet($sale, 'state');

                // If order just did a stockable state transition
                if (
                    OrderStates::hasChangedToStockable($stateCs) ||
                    OrderStates::hasChangedFromStockable($stateCs)
                ) {
                    // Prevent assignments update (handled by the order listener)
                    $doApply = false;
                }
            }
        }

        // If invoice item quantity has changed
        if ($this->persistenceHelper->isChanged($line, 'quantity')) {
            // If order is in stockable state
            if ($doApply && OrderStates::isStockableState($sale->getState())) {
                $this->stockUnitAssigner->applyInvoiceLine($line);
            }

            $this->scheduleInvoiceContentChangeEvent($invoice);
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

        // Detach invoice item to stock units
        if ($invoice->getType() === Model\InvoiceTypes::TYPE_CREDIT) {
            $this->stockUnitAssigner->detachInvoiceLine($item);
        }

        $this->scheduleInvoiceContentChangeEvent($invoice);
    }

    /**
     * Synchronizes the subject identities.
     *
     * @param Model\InvoiceLineInterface $line
     *
     * @return bool
     */
    private function synchronizeSubjectIdentity(Model\InvoiceLineInterface $line)
    {
        if (null === $item = $line->getSaleItem()) {
            return false;
        }

        if (!$item->hasIdentity()) {
            return false;
        }

        if (!$item->getSubjectIdentity()->equals($line->getSubjectIdentity())) {
            $line->getSubjectIdentity()->copy($item->getSubjectIdentity());

            return true;
        }

        return false;
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
            if ($old != $new) {
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
