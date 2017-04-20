<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Order\Model;

use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;

/**
 * Interface OrderItemStockAssignmentInterface
 * @package Ekyna\Component\Commerce\Order\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderItemStockAssignmentInterface extends StockAssignmentInterface
{
    public function getOrderItem(): ?OrderItemInterface;

    /**
     * @return $this|OrderItemStockAssignmentInterface
     */
    public function setOrderItem(?OrderItemInterface $orderItem): OrderItemStockAssignmentInterface;
}
