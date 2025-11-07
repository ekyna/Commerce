<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Interface OrderPrioritizerInterface
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface OrderPrioritizerInterface
{
    /**
     * Prioritizes an order by trying to move stock assignments
     * to make all items shippable.
     *
     * @return bool Whether the order has been prioritized.
     */
    public function prioritize(OrderInterface $item): bool;

    /**
     * Prioritizes an order item recursively
     * by trying to move stock assignments
     * to make the item and its children shippable.
     *
     * @param Decimal|null $quantity  The quantity to prioritize
     * @param bool         $sameOrder Whether to allow to pick quantity from same order's assignments.
     *
     * @return bool Whether the order item has been prioritized.
     */
    public function prioritizeItem(
        OrderItemInterface $item,
        Decimal            $quantity = null,
        bool               $sameOrder = false
    ): bool;
}
