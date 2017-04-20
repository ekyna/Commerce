<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Payment\Resolver;

use DateTimeInterface;
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
     */
    public function isInvoiceDue(InvoiceInterface $invoice): bool;

    /**
     * Resolves the sale's due (outstanding) date.
     */
    public function resolveSaleDueDate(SaleInterface $sale): ?DateTimeInterface;

    /**
     * Resolves the invoice due date.
     */
    public function resolveInvoiceDueDate(InvoiceInterface $invoice): ?DateTimeInterface;
}
