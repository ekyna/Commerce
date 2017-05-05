<?php

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Invoice\Updater\InvoiceUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractInvoiceListener
 * @package Ekyna\Component\Commerce\Invoice\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var NumberGeneratorInterface
     */
    protected $invoiceNumberGenerator;

    /**
     * @var NumberGeneratorInterface
     */
    protected $creditNumberGenerator;

    /**
     * @var InvoiceUpdaterInterface
     */
    protected $updater;


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
     * Sets the invoice number generator.
     *
     * @param NumberGeneratorInterface $generator
     */
    public function setInvoiceNumberGenerator($generator)
    {
        $this->invoiceNumberGenerator = $generator;
    }

    /**
     * Sets the credit number generator.
     *
     * @param NumberGeneratorInterface $generator
     */
    public function setCreditNumberGenerator($generator)
    {
        $this->creditNumberGenerator = $generator;
    }

    /**
     * Sets the invoice updater.
     *
     * @param InvoiceUpdaterInterface $updater
     */
    public function setUpdater(InvoiceUpdaterInterface $updater)
    {
        $this->updater = $updater;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $invoice = $this->getInvoiceFromEvent($event);

        // Generate number and key
        $changed = $this->generateNumber($invoice);

        // Updates the invoice data
        $changed |= $this->updater->updateData($invoice);

        /**
         * TODO Resource behaviors.
         */
        $invoice
            ->setCreatedAt(new \DateTime())
            ->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($invoice, false);
        }

        $sale = $invoice->getSale();
        $sale->addInvoice($invoice); // TODO wtf ?

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $invoice = $this->getInvoiceFromEvent($event);

        $this->preventForbiddenChange($invoice);

        // Generate number and key
        $changed = $this->generateNumber($invoice);

        // Updates the invoice data
        $changed |= $this->updater->updateData($invoice);

        /**
         * TODO Resource behaviors.
         */
        $invoice->setUpdatedAt(new \DateTime());

        if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($invoice, false);

            $this->scheduleSaleContentChangeEvent($invoice->getSale());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $invoice = $this->getInvoiceFromEvent($event);

        $this->scheduleSaleContentChangeEvent($invoice->getSale());
    }

    /**
     * Content change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event)
    {
        $invoice = $this->getInvoiceFromEvent($event);

        if (!$this->persistenceHelper->isScheduledForRemove($invoice)) {
            // Calculate the total.
            $changed = $this->updater->updatePricing($invoice);

            if ($changed) {
                $this->persistenceHelper->persistAndRecompute($invoice, false);

                foreach ($invoice->getLines() as $line) {
                    $this->persistenceHelper->persistAndRecompute($line, false);
                }
            }
        }

        $this->scheduleSaleContentChangeEvent($invoice->getSale());
    }

    /**
     * Generates the number.
     *
     * @param InvoiceInterface $invoice
     *
     * @return bool Whether the invoice number has been generated or not.
     */
    protected function generateNumber(InvoiceInterface $invoice)
    {
        if (0 == strlen($invoice->getNumber())) {
            if ($invoice->getType() === InvoiceTypes::TYPE_INVOICE) {
                $this->invoiceNumberGenerator->generate($invoice);
            } elseif ($invoice->getType() === InvoiceTypes::TYPE_CREDIT) {
                $this->creditNumberGenerator->generate($invoice);
            } else {
                throw new Exception\InvalidArgumentException("Unexpected invoice type.");
            }

            return true;
        }

        return false;
    }

    /**
     * Prevents some of the invoice's fields to change.
     *
     * @param InvoiceInterface $invoice
     */
    protected function preventForbiddenChange(InvoiceInterface $invoice)
    {
        if ($this->persistenceHelper->isChanged($invoice, 'type')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($invoice, 'type');
            if ($old != $new) {
                throw new Exception\RuntimeException("Changing the invoice's type is not yet supported.");
            }
        }
    }

    /**
     * Dispatches the sale content change event.
     *
     * @param SaleInterface $sale
     */
    abstract protected function scheduleSaleContentChangeEvent(SaleInterface $sale);

    /**
     * Returns the invoice from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return InvoiceInterface
     * @throws Exception\InvalidArgumentException
     */
    abstract protected function getInvoiceFromEvent(ResourceEventInterface $event);
}
