<?php

namespace Ekyna\Component\Commerce\Order\Model;

/**
 * Interface OrderInvoiceInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInvoiceInterface
{
    /**
     * Returns the order.
     *
     * @return OrderInterface
     */
    public function getOrder();

    /**
     * Sets the order.
     *
     * @param OrderInterface $order
     *
     * @return $this|OrderInvoiceInterface
     */
    public function setOrder(OrderInterface $order = null);
}
