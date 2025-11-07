<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Interface OrderPrioritizeCheckerInterface
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface OrderPrioritizeCheckerInterface
{
    /**
     * Returns whether the order can be prioritized.
     */
    public function check(OrderInterface $order): bool;

    /**
     * Returns whether the order item can be prioritized.
     */
    public function checkItem(OrderItemInterface $item): bool;
}
