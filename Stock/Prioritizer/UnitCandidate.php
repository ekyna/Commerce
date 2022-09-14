<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Util\Combination;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

use const SORT_NUMERIC;

/**
 * Class UnitCandidate
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class UnitCandidate
{
    /**
     * Builds a new unit candidate.
     */
    public static function build(
        StockUnitInterface $unit,
        SaleItemInterface  $item,
        Decimal            $quantity,
        bool               $sameSale
    ): UnitCandidate {
        $sale = $item->getRootSale();

        /** @var array<int, Decimal> $map */
        $map = [];
        $greater = null;
        foreach ($unit->getStockAssignments() as $a) {
            if ($sameSale) {
                $i = $a->getSaleItem();
                if ($item === $i) {
                    continue;
                }
                if ($sale !== $i->getRootSale()) {
                    continue;
                }
            } elseif ($sale === $a->getSaleItem()->getRootSale()) {
                continue;
            }

            // Skip non-releasable assignment
            if (0 >= $d = $a->getReleasableQuantity()) {
                continue;
            }

            // Perfect assigment
            if ($d->equals($quantity)) {
                // Keep as greater
                $greater = ['id' => $a->getId(), 'quantity' => $d];
                // Clear map
                $map = [];

                break;
            } elseif ($d > $quantity) {
                // Keep greater as separate map
                if (null === $greater || $greater['quantity'] > $d) {
                    $greater = ['id' => $a->getId(), 'quantity' => $d];
                }

                continue;
            }

            $map[$a->getId()] = $d;
        }

        arsort($map, SORT_NUMERIC);

        $releasable = Decimal::sum($map);
        if ($greater && $releasable < $greater['quantity']) {
            $releasable = $greater['quantity'];
        }

        $candidate = new UnitCandidate();

        $candidate->unit = $unit;
        $candidate->shippable = $unit->getShippableQuantity();
        $candidate->reservable = $unit->getReservableQuantity();
        $candidate->releasable = $releasable;
        $candidate->map = $map;
        $candidate->greater = $greater;

        return $candidate;
    }

    public readonly StockUnitInterface $unit;
    public readonly Decimal            $shippable;
    public readonly Decimal            $reservable;
    public readonly Decimal            $releasable;

    /** @var array<int, Decimal> $map */
    private readonly array $map;
    /** @var array{id: int, quantity: Decimal}|null $greater */
    private readonly ?array $greater;

    private ?AssignmentCombination $combination = null;
    private Decimal                $combinationQty;


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
        $combinations = [];

        if ($this->greater) {
            $map = [$this->greater['id'] => $qty = $this->greater['quantity']];
            $combinations[] = new AssignmentCombination($map, $qty->sub($quantity));
        }

        // Map may be empty if greater is a perfect match
        if (empty($this->map)) {
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

        // Size 1 < size <= max
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
