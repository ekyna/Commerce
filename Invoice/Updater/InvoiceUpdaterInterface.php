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
     * Updates the invoice's data (currency, customer and addresses).
     *
     * @param Model\InvoiceInterface $invoice
     *
     * @return bool
     */
    public function update(Model\InvoiceInterface $invoice);
}
