<?php

namespace Ekyna\Component\Commerce\Accounting\Export;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;
use Ekyna\Component\Commerce\Payment\Model\PaymentInterface;

/**
 * Class AbstractAccountingFilter
 * @package Ekyna\Component\Commerce\Accounting\Export
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractAccountingFilter implements AccountingFilterInterface
{
    /**
     * @inheritDoc
     */
    public function filterInvoice(InvoiceInterface $invoice): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function filterPayment(PaymentInterface $payment): bool
    {
        return true;
    }
}
