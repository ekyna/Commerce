<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceInterface;

/**
 * Interface OrderInvoiceInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInvoiceInterface extends InvoiceInterface
{
    /**
     * Returns the order.
     *
     * @return OrderInterface
     */
    public function getOrder(): ?OrderInterface;

    /**
     * Sets the order.
     *
     * @param OrderInterface $order
     *
     * @return $this|OrderInvoiceInterface
     */
    public function setOrder(OrderInterface $order = null): OrderInvoiceInterface;
}
