<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;

/**
 * Interface OrderInvoiceLineInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderInvoiceLineInterface extends InvoiceLineInterface
{
    /**
     * Set the order item.
     *
     * @param OrderItemInterface $item
     *
     * @return $this|OrderInvoiceLineInterface
     */
    public function setOrderItem(OrderItemInterface $item = null);

    /**
     * Returns the order item.
     *
     * @return OrderItemInterface
     */
    public function getOrderItem();

    /**
     * Set the order adjustment.
     *
     * @param OrderAdjustmentInterface $adjustment
     *
     * @return $this|OrderInvoiceLineInterface
     */
    public function setOrderAdjustment(OrderAdjustmentInterface $adjustment = null);

    /**
     * Returns the order adjustment.
     *
     * @return OrderAdjustmentInterface
     */
    public function getOrderAdjustment();
}
