<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;

use function min;

/**
 * Class OrderPrioritizer
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderPrioritizer extends AbstractPrioritizer implements OrderPrioritizerInterface
{
    use OrderCheckerTrait;

    public function prioritize(OrderInterface $item): bool
    {
        if (!$this->checkOrder($item)) {
            return false;
        }

        $changed = false;

        foreach ($item->getItems() as $item) {
            $changed = $this->do($item, null, false, false) || $changed;
        }

        return $changed;
    }

    public function prioritizeItem(
        OrderItemInterface $item,
        Decimal            $quantity = null,
        bool               $sameOrder = false
    ): bool {
        $changed = $this->do($item, $quantity, true, false);

        if ($sameOrder && $quantity && $this->can($item, $quantity)) {
            $changed = $this->do($item, $quantity, true, true) || $changed;
        }

        return $changed;
    }

    protected function do(
        OrderItemInterface $item,
        ?Decimal           $quantity,
        bool               $checkSale,
        bool               $allowSameOrder
    ): bool {
        if ($checkSale && !$this->checkOrder($item->getRootSale())) {
            return false;
        }

        $changed = false;

        foreach ($item->getChildren() as $child) {
            $qty = $quantity?->mul($child->getQuantity());
            $changed = $this->do($child, $qty, false, $allowSameOrder) || $changed;
        }

        $assignments = $item->getStockAssignments();

        if (0 === $assignments->count()) {
            if ($this->unitAssigner->supportsAssignment($item)) {
                $this->unitAssigner->assignOrderItem($item);

                $changed = true;
            }

            return $changed;
        }

        foreach ($item->getStockAssignments() as $assignment) {
            $changed = $this->prioritizeAssignment($assignment, $quantity, $allowSameOrder) || $changed;
        }

        return $changed;
    }

    protected function can(OrderItemInterface $item, Decimal $quantity): bool
    {
        // TODO \Ekyna\Component\Commerce\Stock\Prioritizer\PrioritizeChecker::can ?

        foreach ($item->getChildren() as $child) {
            if ($this->can($child, $quantity->mul($child->getQuantity()))) {
                return true;
            }
        }

        $assignments = $item->getStockAssignments();

        if (0 === $assignments->count()) {
            return $this->unitAssigner->supportsAssignment($item);
        }

        $quantity = min($quantity, $item->getTotalQuantity());
        $sum = new Decimal(0);
        foreach ($assignments as $assignment) {
            $sum = $sum->add($assignment->getShippedQuantity())->add($assignment->getShippableQuantity());
        }

        return $quantity > $sum;
    }
}
