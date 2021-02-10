<?php

namespace Ekyna\Component\Commerce\Accounting\Export;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Interface AccountingFilterInterface
 * @package Ekyna\Component\Commerce\Accounting\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface AccountingFilterInterface
{
    /**
     * Filters the given invoice.
     *
     * @param InvoiceInterface $invoice
     *
     * @return bool Whether the invoice should be exported.
     */
    public function filterInvoice(InvoiceInterface $invoice): bool;

    /**
     * Filters the given payment.
     *
     * @param PaymentInterface $payment
     *
     * @return bool Whether the payment should be exported.
     */
    public function filterPayment(PaymentInterface $payment): bool;
}
