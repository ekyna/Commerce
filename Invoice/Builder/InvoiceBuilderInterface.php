<?php

namespace Ekyna\Component\Commerce\Invoice\Builder;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Interface InvoiceBuilderInterface
 * @package Ekyna\Component\Commerce\Invoice\Builder
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceBuilderInterface
{
    /**
     * Builds the invoice.
     *
     * @param InvoiceInterface $invoice
     */
    public function build(InvoiceInterface $invoice);
}
