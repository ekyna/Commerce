<?php

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Common\Model\SaleItemAdjustmentInterface;

/**
 * Interface OrderItemAdjustmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderItemAdjustmentInterface extends SaleItemAdjustmentInterface
{
    /**
     * Sets the order item.
     *
     * @param OrderItemInterface $item
     *
     * @return $this|OrderAdjustmentInterface
     */
    public function setItem(OrderItemInterface $item = null);
}
