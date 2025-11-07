<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;

/**
 * Class ProductionPrioritizeChecker
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionPrioritizeChecker extends AbstractPrioritizeChecker implements ProductionPrioritizeCheckerInterface
{
    public function check(ProductionOrderInterface $order): bool
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

    public function checkItem(ProductionItemInterface $item): bool
    {
        return $this->can($item, true);
    }

    protected function can(ProductionItemInterface $item, bool $checkSale): bool
    {
        if ($checkSale && !$this->checkOrder($item->getProductionOrder())) {
            return false;
        }

        return $this->checkAssignable($item);
    }

    private function checkOrder(ProductionOrderInterface $order): bool
    {
        return POState::isStockableState($order);
    }
}
