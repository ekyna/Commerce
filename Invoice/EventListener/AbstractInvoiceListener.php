<?php

namespace Ekyna\Component\Commerce\Invoice\EventListener;

use Ekyna\Component\Commerce\Common\Generator\GeneratorInterface;
use Ekyna\Component\Commerce\Common\Model\LockingHelperAwareTrait;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Document\Builder\DocumentBuilderInterface;
use Ekyna\Component\Commerce\Document\Calculator\DocumentCalculatorInterface;
use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceSubjectInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractInvoiceListener
 * @package Ekyna\Component\Commerce\Invoice\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractInvoiceListener
{
    use LockingHelperAwareTrait;

    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var GeneratorInterface
     */
    protected $invoiceNumberGenerator;

    /**
     * @var GeneratorInterface
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
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper): void
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the invoice number generator.
     *
     * @param GeneratorInterface $generator
     */
    public function setInvoiceNumberGenerator(GeneratorInterface $generator): void
    {
        $this->invoiceNumberGenerator = $generator;
    }

    /**
     * Sets the credit number generator.
     *
     * @param GeneratorInterface $generator
     */
    public function setCreditNumberGenerator(GeneratorInterface $generator): void
    {
        $this->creditNumberGenerator = $generator;
    }

    /**
     * Sets the invoice updater.
     *
     * @param DocumentBuilderInterface $builder
     */
    public function setInvoiceBuilder(DocumentBuilderInterface $builder): void
    {
        $this->invoiceBuilder = $builder;
    }

    /**
     * Sets the invoice calculator.
     *
     * @param DocumentCalculatorInterface $calculator
     */
    public function setInvoiceCalculator(DocumentCalculatorInterface $calculator): void
    {
        $this->invoiceCalculator = $calculator;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event): void
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
    public function onUpdate(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        $this->preventForbiddenChange($invoice);

        $this->scheduleSaleContentChangeEvent($this->getSaleFromInvoice($invoice));
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        if ($this->lockingHelper->isLocked($invoice)) {
            throw new Exception\IllegalOperationException(sprintf(
                'Invoice %s is locked.',
                $invoice->getNumber()
            ));
        }

        $sale = $this->getSaleFromInvoice($invoice);

        $sale->removeInvoice($invoice);

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Content change event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onContentChange(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

        if (!$this->persistenceHelper->isScheduledForRemove($invoice)) {
            if ($this->updateTotals($invoice)) {
                $this->persistenceHelper->persistAndRecompute($invoice, false);
            }
        }

        $sale = $this->getSaleFromInvoice($invoice);

        $this->scheduleSaleContentChangeEvent($sale);
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onPreUpdate(ResourceEventInterface $event): void
    {
        $invoice = $this->getInvoiceFromEvent($event);

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
    public function onPreDelete(ResourceEventInterface $event): void
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
    protected function updateTotals(InvoiceInterface $invoice): bool
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
     * Generates the number.
     *
     * @param InvoiceInterface $invoice
     *
     * @return bool Whether the invoice number has been generated or not.
     */
    protected function generateNumber(InvoiceInterface $invoice): bool
    {
        if (!empty($invoice->getNumber())) {
            return false;
        }

        if ($invoice->isCredit()) {
            $invoice->setNumber($this->creditNumberGenerator->generate($invoice));
        } else {
            $invoice->setNumber($this->invoiceNumberGenerator->generate($invoice));
        }

        return true;
    }

    /**
     * Prevents some of the invoice's properties to change.
     *
     * @param InvoiceInterface $invoice
     *
     * @throws Exception\IllegalOperationException
     */
    protected function preventForbiddenChange(InvoiceInterface $invoice): void
    {
        // Only comment, description, paid total and real paid total can change for locked invoices
        $allowed = ['comment', 'description', 'paidTotal', 'realPaidTotal'];
        $cs = $this->persistenceHelper->getChangeSet($invoice);
        if (!empty(array_diff(array_keys($cs), $allowed)) && $this->lockingHelper->isLocked($invoice)) {
            throw new Exception\IllegalOperationException(sprintf(
                'Invoice %s is locked.',
                $invoice->getNumber()
            ));
        }

        if ($this->persistenceHelper->isChanged($invoice, 'type')) {
            [$old, $new] = $this->persistenceHelper->getChangeSet($invoice, 'type');
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
    protected function getSaleFromInvoice(InvoiceInterface $invoice): SaleInterface
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
    abstract protected function scheduleSaleContentChangeEvent(SaleInterface $sale): void;

    /**
     * Returns the invoice from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return InvoiceInterface
     * @throws Exception\InvalidArgumentException
     */
    abstract protected function getInvoiceFromEvent(ResourceEventInterface $event): InvoiceInterface;

    /**
     * Returns the invoice's sale property path.
     *
     * @return string
     */
    abstract protected function getSalePropertyPath(): string;
}
