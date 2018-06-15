<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleNotificationInterface;

/**
 * Interface OrderNotificationInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderNotificationInterface extends SaleNotificationInterface
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
     * @return $this|OrderNotificationInterface
     */
    public function setOrder(OrderInterface $order = null);
}