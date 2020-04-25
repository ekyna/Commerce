<?php

namespace Ekyna\Component\Commerce\Order\Updater;


use Ekyna\Component\Commerce\Order\Model\OrderInterface;

/**
 * Class OrderUpdater
 * @package Ekyna\Component\Commerce\Order\Updater
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface OrderUpdaterInterface
{
    /**
     * Updates the order margin totals (+ revenue).
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the margin total has changed.
     */
    public function updateMarginTotals(OrderInterface $order): bool;

    /**
     * Updates the order items count.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the items count has changed.
     */
    public function updateItemsCount(OrderInterface $order): bool;
}
