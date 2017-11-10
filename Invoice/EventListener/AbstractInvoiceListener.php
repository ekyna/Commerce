<?php

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Customer\Updater\CustomerUpdaterInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
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
     * @var DocumentBuilderInterface
     */
    protected $invoiceBuilder;

    /**
     * @var DocumentCalculatorInterface
     */
    protected $invoiceCalculator;

    /**
     * @var CustomerUpdaterInterface
     */
    protected $customerUpdater;


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
    public function setInvoiceNumberGenerator(NumberGeneratorInterface $generator)
    {
        $this->invoiceNumberGenerator = $generator;
    }

    /**
     * Sets the credit number generator.
     *
     * @param NumberGeneratorInterface $generator
     */
    public function setCreditNumberGenerator(NumberGeneratorInterface $generator)
    {
        $this->creditNumberGenerator = $generator;
    }

    /**
     * Sets the invoice updater.
     *
     * @param DocumentBuilderInterface $invoiceBuilder
     */
    public function setInvoiceBuilder(DocumentBuilderInterface $invoiceBuilder)
    {
        $this->invoiceBuilder = $invoiceBuilder;
    }

    /**
     * Sets the invoice calculator.
     *
     * @param DocumentCalculatorInterface $invoiceCalculator
     */
    public function setInvoiceCalculator(DocumentCalculatorInterface $invoiceCalculator)
    {
        $this->invoiceCalculator = $invoiceCalculator;
    }

    /**
     * Sets the customerUpdater.
     *
     * @param CustomerUpdaterInterface $customerUpdater
     */
    public function setCustomerUpdater(CustomerUpdaterInterface $customerUpdater)
    {
        $this->customerUpdater = $customerUpdater;
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
        $changed |= $this->invoiceBuilder->update($invoice);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($invoice, false);
        }

        //$this->updateCustomerCreditBalance($invoice);

        $sale = $invoice->getSale();
        if ($sale instanceof InvoiceSubjectInterface) {
            $sale->addInvoice($invoice); // TODO wtf ?
        }

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
        $changed |= $this->invoiceBuilder->update($invoice);

        if ($changed) {
            //$this->updateCustomerCreditBalance($invoice);

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

        //$this->updateCustomerCreditBalance($invoice);

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
            $changed = $this->invoiceCalculator->calculate($invoice);

            if ($changed) {
                $this->persistenceHelper->persistAndRecompute($invoice, false);

                foreach ($invoice->getLines() as $line) {
                    $this->persistenceHelper->persistAndRecompute($line, false);
                }
            }
        }

        $this->updateCustomerCreditBalance($invoice);

        $this->scheduleSaleContentChangeEvent($invoice->getSale());
    }

    /**
     * Updates the customer credit balance
     *
     * @param InvoiceInterface $invoice
     */
    protected function updateCustomerCreditBalance(InvoiceInterface $invoice)
    {
        // Abort if not credit
        if (!InvoiceTypes::isCredit($invoice)) {
            return;
        }

        // Abort if no customer
        if (null === $customer = $invoice->getSale()->getCustomer()) {
            return;
        }

        // TODO Multiple call will credit too much !
        if ($this->persistenceHelper->isScheduledForRemove($invoice)) {
            $this->customerUpdater->updateCreditBalance($customer, -$invoice->getGrandTotal(), true);

            return;
        }

        if (empty($cs = $this->persistenceHelper->getChangeSet($invoice, 'grandTotal'))) {
            return;
        }

        if (0 != $amount = $cs[1] - $cs[0]) { // old - new
            $this->customerUpdater->updateCreditBalance($customer, $amount, true);
        }
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
            if (InvoiceTypes::isInvoice($invoice)) {
                $this->invoiceNumberGenerator->generate($invoice);
            } elseif (InvoiceTypes::isCredit($invoice)) {
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
