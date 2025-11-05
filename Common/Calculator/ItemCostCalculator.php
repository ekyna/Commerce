<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Decimal\Decimal;
use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\Cost;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Calculator\AssignableCostCalculatorInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;

/**
 * Class ItemCostCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ItemCostCalculator implements ItemCostCalculatorInterface
{
    public function __construct(
        private readonly AssignableCostCalculatorInterface $calculator,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function calculateSaleItem(SaleItemInterface $item, Decimal $quantity = null, bool $single = false): Cost
    {
        if ($item->isCompound() && !$item->hasPrivateChildren()) {
            return new Cost();
        }

        if ($item->isPrivate() && !$single) {
            return new Cost();
        }

        if (null === $quantity) {
            $quantity = $item->getTotalQuantity();
        }

        $cost = $this->calculateUnitCost($item);

        $cost = $cost->multiply($quantity);

        if (!$single) {
            return $cost->add($this->calculateChildrenCost($item->getChildren(), $quantity));
        }

        return $cost;
    }

    /**
     * @param Collection<SaleItemInterface> $children
     * @param Decimal                       $quantity
     * @return Cost
     */
    private function calculateChildrenCost(Collection $children, Decimal $quantity): Cost
    {
        $total = new Cost();
        foreach ($children as $child) {
            $childQuantity = $child->getQuantity()->mul($quantity);

            if (!($child->isCompound() && !$child->hasPrivateChildren())) {
                $childCost = $this->calculateUnitCost($child);

                $total = $total->add($childCost->multiply($childQuantity));
            }

            $total = $total->add($this->calculateChildrenCost($child->getChildren(), $childQuantity));
        }

        return $total;
    }

    private function calculateUnitCost(SaleItemInterface $item): Cost
    {
        if ($item instanceof AssignableInterface) {
            return $this->calculator->calculateAssignableCost($item);
        }

        return $this->calculator->calculateSubjectCost($item);
    }
}
