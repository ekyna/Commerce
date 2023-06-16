<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Common\Calculator;

use Doctrine\Common\Collections\Collection;
use Ekyna\Component\Commerce\Common\Model\Cost;
use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Subject\Guesser\SubjectCostGuesserInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;

use function spl_object_id;

/**
 * Class ItemCostCalculator
 * @package Ekyna\Component\Commerce\Common\Calculator
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ItemCostCalculator implements ItemCostCalculatorInterface
{
    private array $unitCache = [];
    private array $defaultCache = [];

    public function __construct(
        private readonly SubjectHelperInterface      $subjectHelper,
        private readonly SubjectCostGuesserInterface $subjectCostGuesser,
    ) {
    }

    public function onClear(): void
    {
        $this->unitCache = [];
        $this->defaultCache = [];
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

        $cost = clone $this->calculateUnitCost($item);

        $cost->multiply($quantity);

        if (!$single) {
            $this->addChildren($cost, $item->getChildren(), $quantity);
        }

        return $cost;
    }

    /**
     * @param Cost                          $cost
     * @param Collection<SaleItemInterface> $children
     * @param Decimal                       $quantity
     */
    private function addChildren(Cost $cost, Collection $children, Decimal $quantity): void
    {
        foreach ($children as $child) {
            $childQuantity = $child->getQuantity()->mul($quantity);

            if (!($child->isCompound() && !$child->hasPrivateChildren())) {
                $childCost = clone $this->calculateUnitCost($child);

                $cost->add($childCost->multiply($childQuantity));
            }

            $this->addChildren($cost, $child->getChildren(), $childQuantity);
        }
    }

    private function calculateUnitCost(SaleItemInterface $item): Cost
    {
        $key = spl_object_id($item);

        if (isset($this->unitCache[$key])) {
            return $this->unitCache[$key];
        }

        if (!($item instanceof StockAssignmentsInterface && $item->hasStockAssignments())) {
            return $this->unitCache[$key] = $this->getDefaultCost($item);
        }

        $result = new Cost();
        $qtySum = new Decimal(0);

        foreach ($item->getStockAssignments() as $assignment) {
            if (null === $cost = $this->getAssignmentCost($assignment)) {
                $result->setAverage();

                $cost = clone $this->getDefaultCost($item);
            }

            $qtySum += $qty = $assignment->getSoldQuantity();

            $result->add($cost->multiply($qty));
        }

        if ($qtySum->isZero()) {
            return $this->unitCache[$key] = $this->getDefaultCost($item);
        }

        return $this->unitCache[$key] = $result->divide($qtySum);
    }

    private function getDefaultCost(SaleItemInterface $item): Cost
    {
        $key = spl_object_id($item);

        if (isset($this->defaultCache[$key])) {
            return $this->defaultCache[$key];
        }

        $default = $this->guessItemCost($item) ?? new Cost();
        $default->setAverage();

        return $this->defaultCache[$key] = $default;
    }

    /**
     * Returns the stock assignment unit cost.
     */
    private function getAssignmentCost(StockAssignmentInterface $assignment): ?Cost
    {
        $unit = $assignment->getStockUnit();

        if (null === $unit->getSupplierOrderItem()) {
            return null;
        }

        return new Cost(
            product: $unit->getNetPrice(),
            supply: $unit->getShippingPrice()
        );
    }

    /**
     * Returns the sale item subject unit cost.
     */
    protected function guessItemCost(SaleItemInterface $item): ?Cost
    {
        if (null === $subject = $this->subjectHelper->resolve($item)) {
            return null;
        }

        return $this->subjectCostGuesser->guess($subject);
    }
}
