<?php

namespace Ekyna\Component\Commerce\Invoice\Updater;

use Ekyna\Component\Commerce\Invoice\Model;

/**
 * Interface InvoiceUpdaterInterface
 * @package Ekyna\Component\Commerce\Invoice\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface InvoiceUpdaterInterface
{
    /**
     * Updates the invoice's pricing.
     *
     * @param Model\InvoiceInterface $invoice
     *
     * @return bool Whether the invoice has been changed.
     */
    public function updatePricing(Model\InvoiceInterface $invoice);

    /**
     * Updates the invoice's data (currency, customer and addresses).
     *
     * @param Model\InvoiceInterface $invoice
     *
     * @return bool
     */
    public function updateData(Model\InvoiceInterface $invoice);
}
