<?php

namespace Ekyna\Component\Commerce\Payment\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Interface DueDateResolverInterface
 * @package Ekyna\Component\Commerce\Payment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface DueDateResolverInterface
{
    /**
     * Returns whether the given invoice is due.
     *
     * @param InvoiceInterface $invoice
     *
     * @return bool
     */
    public function isInvoiceDue(InvoiceInterface $invoice): bool;

    /**
     * Resolves the sale's due (outstanding) date.
     *
     * @param SaleInterface $sale
     *
     * @return \DateTime|null
     */
    public function resolveSaleDueDate(SaleInterface $sale): ?\DateTime;

    /**
     * Resolves the invoice due date.
     *
     * @param InvoiceInterface $invoice
     *
     * @return \DateTime|null
     */
    public function resolveInvoiceDueDate(InvoiceInterface $invoice): ?\DateTime;
}
