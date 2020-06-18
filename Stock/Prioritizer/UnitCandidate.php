<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Common\Util\Combination;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class UnitCandidate
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UnitCandidate
{
    /**
     * Builds a new unit candidate.
     *
     * @param StockUnitInterface $unit
     * @param SaleInterface      $sale
     *
     * @return UnitCandidate
     */
    public static function build(StockUnitInterface $unit, SaleInterface $sale): self
    {
        $releasable = 0;
        $map = [];
        foreach ($unit->getStockAssignments() as $a) {
            if ($sale === $s = $a->getSaleItem()->getSale()) {
                continue;
            }

            if (0 < $d = $a->getReleasableQuantity()) {
                $releasable += $d;
                $map[$a->getId()] = $d;
            }
        }

        arsort($map, \SORT_NUMERIC);

        $candidate = new static;

        $candidate->unit = $unit;
        $candidate->shippable = $unit->getShippableQuantity();
        $candidate->reservable = $unit->getReservableQuantity();
        $candidate->releasable = $releasable;
        $candidate->map = $map;

        return $candidate;
    }

    /**
     * @var StockUnitInterface
     */
    public $unit;

    /**
     * @var float
     */
    public $shippable;

    /**
     * @var float
     */
    public $reservable;

    /**
     * @var float
     */
    public $releasable;

    /**
     * @var array
     */
    public $map;

    /**
     * @var AssignmentCombination
     */
    public $combination;

    /**
     * @var float
     */
    private $combinationQty;


    /**
     * Returns the best assignments combination for the given quantity.
     *
     * @param float $quantity
     *
     * @return AssignmentCombination
     */
    public function getCombination($quantity)
    {
        if ($this->combination && 0 === bccomp($quantity, $this->combinationQty, 5)) { // TODO precision ?
            return $this->combination;
        }

        $this->combinationQty = $quantity;
        $combinations = $this->buildCombinations($quantity);

        return $this->combination = $this->selectCombination($combinations);
    }

    /**
     * Returns the stock assignment by its ID.
     *
     * @param int $id
     *
     * @return \Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface|null
     */
    public function getAssignmentById($id)
    {
        foreach ($this->unit->getStockAssignments() as &$assignment) {
            if ($assignment->getId() === $id) {
                return $assignment;
            }
        }

        return null;
    }

    /**
     * Builds the assignments releasable quantity combination list.
     *
     * @param float $quantity
     *
     * @return AssignmentCombination[]
     */
    private function buildCombinations($quantity)
    {
        if (empty($this->map)) {
            return [];
        }

        $combinations = [];

        // Size max
        $combination = new AssignmentCombination($this->map, $diff = array_sum($this->map) - $quantity);

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
            $combination = new AssignmentCombination([$id => $qty], $diff = $qty - $quantity);

            // Perfect combination
            if ($diff == 0) {
                return [$combination];
            }

            $combinations[] = $combination;
        }

        // Size 1 < size < max
        for ($length = 2; $length < count($this->map); $length++) {
            foreach (Combination::generateAssoc($this->map, $length) as $map) {
                $combination = new AssignmentCombination($map, $diff = array_sum($map) - $quantity);

                // Perfect combination
                if ($diff == 0) {
                    return [$combination];
                }

                $combinations[] = $combination;
            }
        }

        return $combinations;
    }

    /**
     * Select the best combination.
     *
     * @param array $combinations
     *
     * @return AssignmentCombination|null
     */
    private function selectCombination(array $combinations): ?AssignmentCombination
    {
        if (empty($combinations)) {
            return null;
        }

        // Sort combinations: prefer closest then greater
        usort($combinations, function (AssignmentCombination $a, AssignmentCombination $b) {
            if (0 < $a->diff && 0 < $b->diff) {
                return abs($b->diff) - abs($a->diff);
            }

            return $b->diff - $a->diff;
        });

        return reset($combinations);
    }
}
