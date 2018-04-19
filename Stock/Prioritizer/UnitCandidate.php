<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
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
    public static function build(StockUnitInterface $unit, SaleInterface $sale)
    {
        $releasable = 0;
        $map = [];
        foreach ($unit->getStockAssignments() as $a) {
            // Ignore assignments from the same sale (Should be impossible)
            if ($sale === $a->getSaleItem()->getSale()) {
                continue;
            }

            // Ignore assignments from preparation sales
            /** @var \Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface $sale */
            if ($sale->getShipmentState() === ShipmentStates::STATE_PREPARATION) {
                continue;
            }

            if (0 < $d = $a->getSoldQuantity() - $a->getShippedQuantity()) {
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
     * Returns the best assignments combination for the given quantity.
     *
     * @param float $quantity
     * @param bool  $reset
     *
     * @return AssignmentCombination
     */
    public function getCombination($quantity, $reset = false)
    {
        if (null !== $this->combination && !$reset) {
            return $this->combination;
        }

        $this->combination = null;

        if (!empty($combinations = $this->buildCombinations($quantity))) {
            // Sort combinations: prefer closest, then greater, then lower, finally smaller
            usort($combinations, function (AssignmentCombination $a, AssignmentCombination $b) use ($quantity) {
                if ($a->diff == $b->diff) {
                    if ($a->size == $b->size) {
                        return 0;
                    }

                    return $a->size < $b->size ? -1 : 1;
                }

                if (0 <= $a->diff) {
                    return intval(0 > $b->diff ? -1 : $a->diff - $b->diff);
                }

                return intval(0 < $b->diff ? 1 : $b->diff - $a->diff);
            });

            $this->combination = reset($combinations);
        }

        return $this->combination;
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

        // Size 1
        foreach ($this->map as $id => $qty) {
            $combinations[] = new AssignmentCombination([$id => $qty], $diff = $qty - $quantity);

            if ($diff == 0) {
                return $combinations;
            }
        }

        // Size 1 < size < max
        for ($length = 2; $length < count($this->map); $length++) {
            foreach (combine_assoc($this->map, $length) as $map) {
                $combinations[] = new AssignmentCombination($map, $diff = array_sum($map) - $quantity);

                if ($diff == 0) {
                    return $combinations;
                }
            }
        }

        // Size max
        $combinations[] = new AssignmentCombination($this->map, array_sum($this->map) - $quantity);

        return $combinations;
    }
}

/**
 * Returns the unique combinations of the given values.
 *
 * @param array $values
 * @param int   $length
 *
 * @return \Generator
 */
function combine(array $values, $length)
{
    $originalLength = count($values);
    $remainingLength = $originalLength - $length + 1;
    for ($i = 0; $i < $remainingLength; ++$i) {
        $current = $values[$i];
        if (1 === $length) {
            yield [$current];
        } else {
            $remaining = array_slice($values, $i + 1);
            foreach (combine($remaining, $length - 1) as $permutation) {
                array_unshift($permutation, $current);
                yield $permutation;
            }
        }
    }
}

/**
 * Returns the unique combinations of the given values, preserving keys.
 *
 * @param array $values
 * @param int   $length
 *
 * @return \Generator
 */
function combine_assoc(array $values, $length)
{
    foreach (combine(array_keys($values), $length) as $combination) {
        $result = [];
        foreach ($combination as $key) {
            $result[$key] = $values[$key];
        }
        yield $result;
    }
}