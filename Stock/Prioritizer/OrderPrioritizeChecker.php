<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

/**
 * Class OrderPrioritizeChecker
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OrderPrioritizeChecker extends AbstractPrioritizeChecker implements OrderPrioritizeCheckerInterface
{
    use OrderCheckerTrait;

    public function check(OrderInterface $order): bool
    {
        if (!$this->checkOrder($order)) {
            return false;
        }

        foreach ($order->getItems() as $item) {
            if ($this->can($item, false)) {
                return true;
            }
        }

        return false;
    }

    public function checkItem(OrderItemInterface $item): bool
    {
        return $this->can($item, true);
    }

    protected function can(OrderItemInterface $item, bool $checkSale): bool
    {
        if ($checkSale && !$this->checkOrder($item->getRootSale())) {
            return false;
        }

        foreach ($item->getChildren() as $child) {
            if ($this->can($child, false)) {
                return true;
            }
        }

        return $this->checkAssignable($item);
    }
}
