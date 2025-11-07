<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Manufacture\Model\POState;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionOrderInterface;

/**
 * Class ProductionPrioritizer
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ProductionPrioritizer extends AbstractPrioritizer implements ProductionPrioritizerInterface
{
    public function prioritize(ProductionOrderInterface $order): bool
    {
        if (!$this->checkOrder($order)) {
            return false;
        }

        $changed = false;

        foreach ($order->getItems() as $item) {
            $changed = $this->do($item, false) || $changed;
        }

        return $changed;
    }

    public function prioritizeItem(ProductionItemInterface $item): bool
    {
        return $this->do($item, true);
    }

    protected function do(ProductionItemInterface $item, bool $checkOrder): bool
    {
        if ($checkOrder && !$this->checkOrder($item->getProductionOrder())) {
            return false;
        }

        $changed = false;
        $assignments = $item->getStockAssignments();

        if (0 === $assignments->count()) {
            if ($this->unitAssigner->supportsAssignment($item)) {
                $this->unitAssigner->assignProductionItem($item);

                $changed = true;
            }

            return $changed;
        }

        foreach ($item->getStockAssignments() as $assignment) {
            $changed = $this->prioritizeAssignment($assignment, null) || $changed;
        }

        return $changed;
    }

    private function checkOrder(ProductionOrderInterface $order): bool
    {
        return POState::isStockableState($order);
    }
}
