<?php

namespace Ekyna\Component\Commerce\Invoice\Model;

/**
 * Interface InvoiceSubjectInterface
 * @package Ekyna\Component\Commerce\Invoice\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceSubjectInterface
{
    /**
     * Returns the invoice total.
     *
     * @return float
     */
    public function getInvoiceTotal();

    /**
     * Sets the invoice total.
     *
     * @param float $total
     *
     * @return InvoiceSubjectTrait
     */
    public function setInvoiceTotal($total);

    /**
     * Returns the credits total.
     *
     * @return float
     */
    public function getCreditTotal();

    /**
     * Sets the credit total.
     *
     * @param float $total
     *
     * @return $this|InvoiceSubjectInterface
     */
    public function setCreditTotal($total);

    /**
     * Returns the invoice state.
     *
     * @return string
     */
    public function getInvoiceState();

    /**
     * Sets the invoice state.
     *
     * @param string $state
     *
     * @return $this|InvoiceSubjectInterface
     */
    public function setInvoiceState($state);

    /**
     * Returns whether the order has invoices or not.
     *
     * @return bool
     */
    public function hasInvoices();

    /**
     * Returns the invoices.
     *
     * @param bool $filter TRUE for invoices, FALSE for credits, NULL for all
     *
     * @return \Doctrine\Common\Collections\Collection|InvoiceInterface[]
     */
    public function getInvoices($filter = null);

    /**
     * Returns whether the order has the invoice or not.
     *
     * @param InvoiceInterface $invoice
     *
     * @return bool
     */
    public function hasInvoice(InvoiceInterface $invoice);

    /**
     * Adds the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return $this|InvoiceSubjectInterface
     */
    public function addInvoice(InvoiceInterface $invoice);

    /**
     * Removes the invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return $this|InvoiceSubjectInterface
     */
    public function removeInvoice(InvoiceInterface $invoice);

    /**
     * Returns the first invoice date.
     *
     * @return \DateTime|null
     */
    public function getInvoicedAt();
}
