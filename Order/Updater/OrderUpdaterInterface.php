<?php

declare(strict_types=1);

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
     * Updates the order margin.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the margin has changed.
     */
    public function updateMargin(OrderInterface $order): bool;

    /**
     * Updates the order items count.
     *
     * @param OrderInterface $order
     *
     * @return bool Whether the items count has changed.
     */
    public function updateItemsCount(OrderInterface $order): bool;
}
