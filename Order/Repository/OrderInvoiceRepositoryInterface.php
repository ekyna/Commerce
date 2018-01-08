<?php

namespace Ekyna\Component\Commerce\Order\Repository;

use Ekyna\Component\Commerce\Invoice\Repository\InvoiceRepositoryInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceInterface;

/**
 * Interface OrderInvoiceRepositoryInterface
 * @package Ekyna\Component\Commerce\Order\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInvoiceRepositoryInterface extends InvoiceRepositoryInterface
{
    /**
     * Creates a new order invoice instance.
     *
     * @return OrderInvoiceInterface
     */
    public function createNew();
}