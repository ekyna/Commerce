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
     * Returns whether the order has invoices or not.
     *
     * @return bool
     */
    public function hasInvoices();

    /**
     * Returns the invoices.
     *
     * @return \Doctrine\Common\Collections\Collection|InvoiceInterface[]
     */
    public function getInvoices();

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
}