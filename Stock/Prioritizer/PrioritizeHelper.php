<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;

/**
 * Class PrioritizeHelper
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 * @TODO rename to PrioritizeUnitGuesser ? or PrioritizeUnitResolver ?
 */
class PrioritizeHelper
{
    /**
     * @var StockUnitResolverInterface
     */
    protected $unitResolver;

    /**
     * @var StockUnitCacheInterface
     */
    protected $unitCache;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface $unitResolver
     * @param StockUnitCacheInterface    $unitCache
     */
    public function __construct(StockUnitResolverInterface $unitResolver, StockUnitCacheInterface $unitCache)
    {
        $this->unitResolver = $unitResolver;
        $this->unitCache    = $unitCache;
    }

    /**
     * Finds the best stock unit to move/merge assignment(s) into for the given quantity.
     *
     * @param StockAssignmentInterface $assignment
     * @param float                    $quantity
     *
     * @return UnitCandidate|null
     */
    public function getUnitCandidate(StockAssignmentInterface $assignment, $quantity): ?UnitCandidate
    {
        $stockUnit = $assignment->getStockUnit();
        $subject = $stockUnit->getSubject();

        // Find the subject's ready stock units
        if (empty($units = $this->unitResolver->findReady($subject))) {
            return null;
        };

        $sale = $assignment->getSaleItem()->getSale();

        $candidates = [];

        foreach ($units as $unit) {
            if ($stockUnit->getId() === $unit->getId()) {
                continue;
            }

            $this->unitCache->add($unit);

            $candidate = UnitCandidate::build($unit, $sale);

            // Skip if no reservable and no releasable quantity
            if ((0 >= $candidate->reservable) && (0 >= $candidate->releasable)) {
                continue;
            }

            $add = false;
            $diff = $quantity - $candidate->reservable;
            if (0 < $candidate->reservable) {
                // Unit has enough reservable quantity
                if (empty($candidates) && 0 >= $diff) {
                    return $candidate;
                }

                $add = true;
            }

            if (0 < $release = min($diff, $candidate->releasable)) {
                if (null !== $combination = $candidate->getCombination($release)) {
                    // Unit has enough reservable + releasable quantity
                    if (empty($candidates) && $combination->sum == $diff) {
                        return $candidate;
                    }
                    $add = true;
                }
            }

            if ($add) {
                $candidates[] = $candidate;
            }
        }

        if (1 === count($candidates)) {
            return reset($candidates);
        }
        if (0 === count($candidates)) {
            return null;
        }

        // Sort candidates
        usort($candidates, function (UnitCandidate $a, UnitCandidate $b) use ($quantity) {
            // TODO sort by (reservable + releasable) DESC

            // TODO Simplify. Prefer:
            // - 'shippable' enough
            // - 'reservable' eq/positive/closest
            // - 'releasable' eq/positive/closest

            // Prefer units with shippable >= quantity, reservable >= quantity or releasable >= quantity
            foreach (['shippable', 'reservable', 'releasable'] as $property) {
                if (false !== $r = $this->ceilComparison($a, $b, $property, $quantity)) {
                    return $r;
                }
            }
            // Prefer units with shippable == quantity, reservable == quantity or releasable == quantity
            foreach (['reservable', 'releasable'] as $property) { // 'shippable' ?
                if (false !== $r = $this->equalComparison($a, $b, $property, $quantity)) {
                    return $r;
                }
            }

            // TODO (temporary)
            foreach (['shippable', 'reservable', 'releasable'] as $property) {
                if ($a->{$property} != $b->{$property}) {
                    return $b->{$property} - $a->{$property};
                }
            }
            return 0;

            // Prefer units with assignments combination's releasable quantity (sum) that
            // equals or is greater than (prefer closest) aimed quantity.
            if ($a->combination->sum == $b->combination->sum) {
                return 0;
            }
            if (0 <= $a->combination->sum) {
                return intval(0 > $b->combination->sum ? -1 : $a->combination->sum - $b->combination->sum);
            }

            return intval(0 < $b->combination->sum ? 1 : $b->combination->sum - $a->combination->sum);
        });

        return reset($candidates);
    }

    /**
     * Returns -1 if A's property is greater than or equal quantity but not B.
     * Returns 1 if B's property is greater than or equal quantity but not A.
     * Else returns false.
     *
     * @param UnitCandidate $a
     * @param UnitCandidate $b
     * @param string        $property
     * @param float         $quantity
     *
     * @return bool|int
     */
    protected function ceilComparison(UnitCandidate $a, UnitCandidate $b, $property, $quantity)
    {
        if ($a->{$property} >= $quantity && $b->{$property} < $quantity) {
            return -1;
        }
        if ($a->{$property} < $quantity && $b->{$property} >= $quantity) {
            return 1;
        }

        return false;
    }

    /**
     * Returns -1 if A's equals quantity but not B.
     * Returns 1 if B's equals quantity but not A.
     * Else returns false.
     *
     * @param UnitCandidate $a
     * @param UnitCandidate $b
     * @param string        $property
     * @param float         $quantity
     *
     * @return bool|int
     */
    protected function equalComparison(UnitCandidate $a, UnitCandidate $b, $property, $quantity)
    {
        if ($a->{$property} == $quantity && $b->{$property} != $quantity) {
            return -1;
        }
        if ($a->{$property} != $quantity && $b->{$property} == $quantity) {
            return 1;
        }

        return false;
    }

//    /**
//     * Returns -1 if A's property is greater than
//     *
//     * @param UnitCandidate $a
//     * @param UnitCandidate $b
//     * @param string        $property
//     *
//     * @return bool|int
//     */
//    private function greaterThanComparison(UnitCandidate $a, UnitCandidate $b, $property)
//    {
//        if ($a->{$property} > $b->{$property}) {
//            return -1;
//        }
//        if ($a->{$property} < $b->{$property}) {
//            return 1;
//        }
//
//        return false;
//    }
}
