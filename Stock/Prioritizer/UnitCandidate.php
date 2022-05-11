<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Combination;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

use const SORT_NUMERIC;

/**
 * Class UnitCandidate
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitCandidate
{
    /**
     * Builds a new unit candidate.
     */
    public static function build(StockUnitInterface $unit, SaleInterface $sale, $quantity): self
    {
        /** @var array<int, Decimal> $map */
        $map = [];
        $greaterFound = false;
        foreach ($unit->getStockAssignments() as $a) {
            if ($sale === $a->getSaleItem()->getSale()) {
                continue;
            }

            // Skip non-releasable assignment
            if (0 >= $d = $a->getReleasableQuantity()) {
                continue;
            }

            if ($d > $quantity) {
                if ($greaterFound) {
                    // Skip if we already have assignment with enough quantity
                    continue;
                }

                $greaterFound = true;
            }

            $map[$a->getId()] = $d;
        }

        arsort($map, SORT_NUMERIC);

        $candidate = new static();

        $candidate->unit = $unit;
        $candidate->shippable = $unit->getShippableQuantity();
        $candidate->reservable = $unit->getReservableQuantity();
        $candidate->releasable = Decimal::sum($map);
        $candidate->map = $map;

        return $candidate;
    }

    public StockUnitInterface    $unit;
    public Decimal               $shippable;
    public Decimal               $reservable;
    public Decimal               $releasable;
    /** @var array<int, Decimal>  */
    public array                 $map;
    public ?AssignmentCombination $combination = null;
    private Decimal              $combinationQty;


    /**
     * Returns the best assignment combination for the given quantity.
     */
    public function getCombination(Decimal $quantity): ?AssignmentCombination
    {
        if ($this->combination && $quantity->equals($this->combinationQty)) { // TODO precision ?
            return $this->combination;
        }

        $this->combinationQty = $quantity;
        $combinations = $this->buildCombinations($quantity);

        return $this->combination = $this->selectCombination($combinations);
    }

    /**
     * Returns the stock assignment by its ID.
     */
    public function getAssignmentById(int $id): ?StockAssignmentInterface
    {
        foreach ($this->unit->getStockAssignments() as $assignment) {
            if ($assignment->getId() === $id) {
                return $assignment;
            }
        }

        return null;
    }

    /**
     * Builds the assignments releasable quantity combination list.
     *
     * @return array<AssignmentCombination>
     */
    private function buildCombinations(Decimal $quantity): array
    {
        if (empty($this->map)) {
            return [];
        }

        $combinations = [];

        // Size max
        $combination = new AssignmentCombination($this->map, $diff = Decimal::sum($this->map)->sub($quantity));

        // Perfect combination or not enough assignments : no need to calculate more combinations
        if ($diff <= 0) {
            return [$combination];
        }

        $combinations[] = $combination;

        if (2 >= count($this->map)) {
            return $combinations;
        }

        // Size 1
        foreach ($this->map as $id => $qty) {
            $combination = new AssignmentCombination([$id => $qty], $diff = $qty->sub($quantity));

            // Perfect combination
            if ($diff->isZero()) {
                return [$combination];
            }

            $combinations[] = $combination;

            if ($diff < 0) {
                // Not enough -> increase combination length
                break;
            }
        }

        // Size 1 < size < max
        for ($length = 2; $length < count($this->map); $length++) {
            foreach (Combination::generateAssoc($this->map, $length) as $map) {
                $combination = new AssignmentCombination($map, $diff = Decimal::sum($map)->sub($quantity));

                // Perfect combination
                if ($diff->isZero()) {
                    return [$combination];
                }

                $combinations[] = $combination;

                if ($diff < 0) {
                    // Not enough -> increase combination length
                    continue 2;
                }
            }
        }

        return $combinations;
    }

    /**
     * Select the best combination.
     */
    private function selectCombination(array $combinations): ?AssignmentCombination
    {
        if (empty($combinations)) {
            return null;
        }

        // Sort combinations: prefer closest, then greater
        usort($combinations, function (AssignmentCombination $a, AssignmentCombination $b) {
            if (0 < $a->diff && 0 < $b->diff) {
                return $b->diff->abs()->sub($a->diff->abs());
            }

            return $b->diff->sub($a->diff);
        });

        return reset($combinations);
    }
}
