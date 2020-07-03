<?php

namespace Ekyna\Component\Commerce\Invoice\Resolver;

use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Interface InvoicePaymentResolverInterface
 * @package Ekyna\Component\Commerce\Invoice\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoicePaymentResolverInterface
{
    /**
     * Resolves the invoice's payments.
     *
     * @param Model\InvoiceInterface $invoice
     * @param bool                   $invoices
     *
     * @return Model\InvoicePayment[]
     */
    public function resolve(Model\InvoiceInterface $invoice, bool $invoices = true): array;

    /**
     * Returns the invoice's paid total.
     *
     * @param Model\InvoiceInterface $invoice
     *
     * @return float
     */
    public function getPaidTotal(Model\InvoiceInterface $invoice): float;

    /**
     * Returns the invoice's real paid total (default currency).
     *
     * @param Model\InvoiceInterface $invoice
     *
     * @return float
     */
    public function getRealPaidTotal(Model\InvoiceInterface $invoice): float;
}
