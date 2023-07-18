<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Manager;

use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;

/**
 * Interface OrderInvoiceManagerInterface
 * @package Ekyna\Component\Commerce\Order\Manager
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface OrderInvoiceManagerInterface extends ResourceManagerInterface
{
    /**
     * Updates the order invoice margin directly in the database (not using ORM).
     *
     * @param OrderInvoiceInterface $invoice
     * @return void
     */
    public function updateMargin(OrderInvoiceInterface $invoice): void;
}
