<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Updater;

use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;

/**
 * Interface OrderInvoiceUpdaterInterface
 * @package Ekyna\Component\Commerce\Order\Updater
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface OrderInvoiceUpdaterInterface
{
    /**
     * Updates the order invoice margin.
     *
     * @param OrderInvoiceInterface $invoice
     * @return bool
     */
    public function updateMargin(OrderInvoiceInterface $invoice): bool;
}
