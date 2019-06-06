<?php

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Common\Generator\NumberGeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Money;
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
     * @param DocumentBuilderInterface $builder
     */
    public function setInvoiceBuilder(DocumentBuilderInterface $builder)
    {
        $this->invoiceBuilder = $builder;
    }

    /**
     * Sets the invoice calculator.
     *
     * @param DocumentCalculatorInterface $calculator
     */
    public function setInvoiceCalculator(DocumentCalculatorInterface $calculator)
    {
        $this->invoiceCalculator = $calculator;
    }

    /**
     * Sets the customer updater.
     *
     * @param CustomerUpdaterInterface $updater
     */
    public function setCustomerUpdater(CustomerUpdaterInterface $updater)
    {
        $this->customerUpdater = $updater;
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

        // Updates the totals
        $changed |= $this->updateTotals($invoice);

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($invoice, false);
        }

        $sale = $this->getSaleFromInvoice($invoice);
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

        // Updates the totals
        $changed |= $this->updateTotals($invoice);

        if ($this->persistenceHelper->isChanged($invoice, 'paymentMethod')) {
            $this->updateCustomerBalance($invoice);
        }

        if ($changed) {
            $this->persistenceHelper->persistAndRecompute($invoice, false);

            $this->scheduleSaleContentChangeEvent($this->getSaleFromInvoice($invoice));
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

        $this->updateCustomerBalance($invoice);

        $sale = $this->getSaleFromInvoice($invoice);

        $sale->removeInvoice($invoice);

        $this->scheduleSaleContentChangeEvent($sale);
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
            if ($this->updateTotals($invoice)) {
                $this->persistenceHelper->persistAndRecompute($invoice, false);
            }

            $this->updateCustomerBalance($invoice);
        }

        $sale = $this->getSaleFromInvoice($invoice);

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        $invoice = $this->getInvoiceFromEvent($event);

        /*if (null !== $invoice->getShipment()) {
            throw new Exception\IllegalOperationException(
                "Invoice (or credit) associated with a shipment (or return) can't be modified."
            );
        }*/

        // Pre load sale's invoices collection
        /** @var InvoiceSubjectInterface $sale */
        $sale = $invoice->getSale();
        $sale->getInvoices()->toArray();
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $invoice = $this->getInvoiceFromEvent($event);

        // Pre load sale's invoices collection
        /** @var InvoiceSubjectInterface $sale */
        $sale = $invoice->getSale();
        $sale->getInvoices()->toArray();
    }

    /**
     * Updates the invoice totals.
     *
     * @param InvoiceInterface $invoice
     *
     * @return bool
     */
    protected function updateTotals(InvoiceInterface $invoice)
    {
        $changed = $this->invoiceCalculator->calculate($invoice);

        if ($changed) {
            foreach ($invoice->getLines() as $line) {
                $this->persistenceHelper->persistAndRecompute($line, false);
            }
        }

        return $changed;
    }

    /**
     * Updates the customer balance
     *
     * @param InvoiceInterface $invoice
     */
    protected function updateCustomerBalance(InvoiceInterface $invoice)
    {
        // Abort if not credit
        if (!InvoiceTypes::isCredit($invoice)) {
            return;
        }

        $sale = $this->getSaleFromInvoice($invoice);

        // Abort if no customer
        if (null === $customer = $sale->getCustomer()) {
            return;
        }

        $methodCs = $this->persistenceHelper->getChangeSet($invoice, 'paymentMethod');
        $amountCs = $this->persistenceHelper->getChangeSet($invoice, 'grandTotal');

        // Debit grand total if invoice is removed
        // TODO Multiple call will credit too much !
        if ($this->persistenceHelper->isScheduledForRemove($invoice)) {
            $method = empty($methodCs) ? $invoice->getPaymentMethod() : $methodCs[0];
            $amount = empty($amountCs) ? $invoice->getGrandTotal(): $amountCs[0];

            if ($method && $method->isCredit() && 0 != Money::compare($amount, 0, $invoice->getCurrency())) {
                $this->customerUpdater->updateCreditBalance($customer, -$amount, true);
            }

            return;
        }

        // Abort if nothing has changed
        if (empty($methodCs) && empty($amountCs)) {
            return;
        }

        // Debit old method customer balance
        /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface $method */
        if (!empty($methodCs) && null !== $method = $methodCs[0]) {
            $amount = empty($amountCs) ? $invoice->getGrandTotal(): $amountCs[0];

            if ($method->isCredit() && 0 != Money::compare($amount, 0, $invoice->getCurrency())) {
                $this->customerUpdater->updateCreditBalance($customer, -$amount, true);
            }
        }

        // Credit new method customer balance
        if (empty($methodCs)) {
            $method = $invoice->getPaymentMethod();
            $amount = empty($amountCs) ? $invoice->getGrandTotal(): $amountCs[1] - $amountCs[0];
        } else {
            /** @var \Ekyna\Component\Commerce\Payment\Model\PaymentMethodInterface $method */
            $method = $methodCs[1];
            $amount = empty($amountCs) ? $invoice->getGrandTotal(): $amountCs[1];
        }
        if ($method && $method->isCredit() && 0 != Money::compare($amount, 0, $invoice->getCurrency())) {
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
     *
     * @throws Exception\IllegalOperationException
     */
    protected function preventForbiddenChange(InvoiceInterface $invoice)
    {
        if ($this->persistenceHelper->isChanged($invoice, 'type')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($invoice, 'type');
            if ($old != $new) {
                throw new Exception\IllegalOperationException(
                    "Changing the invoice type is not yet supported."
                );
            }
        }
    }

    /**
     * Returns the invoice's sale.
     *
     * @param InvoiceInterface $invoice
     *
     * @return SaleInterface|InvoiceSubjectInterface
     */
    protected function getSaleFromInvoice(InvoiceInterface $invoice)
    {
        if (null === $sale = $invoice->getSale()) {
            $cs = $this->persistenceHelper->getChangeSet($invoice, $this->getSalePropertyPath());
            if (!empty($cs)) {
                $sale = $cs[0];
            }
        }

        if (!$sale instanceof SaleInterface) {
            throw new Exception\RuntimeException("Failed to retrieve invoice's sale.");
        }

        return $sale;
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

    /**
     * Returns the invoice's sale property path.
     *
     * @return string
     */
    abstract protected function getSalePropertyPath();
}
