<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;

/**
 * Interface OrderItemInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method OrderInterface getSale()
 */
interface OrderItemInterface extends SaleItemInterface, StockAssignmentsInterface
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
     * @return $this|OrderItemInterface
     */
    public function setOrder(OrderInterface $order = null);
}
