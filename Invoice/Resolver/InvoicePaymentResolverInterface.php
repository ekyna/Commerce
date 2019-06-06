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
     *
     * @return Model\InvoicePayment[]
     */
    public function resolve(Model\InvoiceInterface $invoice): array;

    /**
     * Returns the invoice's paid total.
     *
     * @param Model\InvoiceInterface $invoice
     *
     * @return float
     */
    public function getPaidTotal(Model\InvoiceInterface $invoice): float;
}
