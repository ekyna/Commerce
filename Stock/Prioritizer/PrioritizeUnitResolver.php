<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Stock\Cache\StockUnitCacheInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;

/**
 * Class PrioritizeUnitResolver
 * @package Ekyna\Component\Commerce\Stock\Prioritizer
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class PrioritizeUnitResolver
{
    public function __construct(
        private readonly StockUnitResolverInterface $unitResolver,
        private readonly StockUnitCacheInterface    $unitCache,
        private readonly bool                       $sameSale
    ) {
    }

    /**
     * Finds the best stock unit to move/merge assignment(s) into for the given quantity.
     */
    public function getUnitCandidate(AssignmentInterface $assignment, Decimal $quantity): ?UnitCandidate
    {
        $stockUnit = $assignment->getStockUnit();
        $subject = $stockUnit->getSubject();

        // Find the subject's ready stock units
        if (empty($units = $this->unitResolver->findReady($subject))) {
            return null;
        }

        $assignable = $assignment->getAssignable();

        $candidates = [];

        foreach ($units as $unit) {
            if ($stockUnit->getId() === $unit->getId()) {
                continue;
            }

            $this->unitCache->add($unit);

            $candidate = UnitCandidate::build($unit, $assignable, $quantity, $this->sameSale);

            // Skip if no reservable and no releasable quantity
            if ((0 >= $candidate->reservable) && (0 >= $candidate->releasable)) {
                continue;
            }

            $diff = $quantity - $candidate->reservable;
            if (0 < $candidate->reservable) {
                // Unit has enough reservable quantity
                if (empty($candidates) && 0 >= $diff) {
                    return $candidate;
                }

                $candidates[] = $candidate;

                continue;
            }

            if (0 >= $release = min($diff, $candidate->releasable)) {
                continue;
            }

            if (null === $combination = $candidate->getCombination($release)) {
                continue;
            }

            // Unit has enough reservable + releasable quantity
            if (empty($candidates) && $combination->sum->equals($diff)) {
                return $candidate;
            }

            $candidates[] = $candidate;
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
                if (0 !== $r = $this->ceilComparison($a, $b, $property, $quantity)) {
                    return $r;
                }
            }
            // Prefer units with shippable == quantity, reservable == quantity or releasable == quantity
            foreach (['reservable', 'releasable'] as $property) { // 'shippable' ?
                if (0 !== $r = $this->equalComparison($a, $b, $property, $quantity)) {
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
            /* TODO (?) if ($a->combination->sum == $b->combination->sum) {
                return 0;
            }
            if (0 <= $a->combination->sum) {
                return intval(0 > $b->combination->sum ? -1 : $a->combination->sum - $b->combination->sum);
            }

            return intval(0 < $b->combination->sum ? 1 : $b->combination->sum - $a->combination->sum);*/
        });

        return reset($candidates);
    }

    /**
     * Returns -1 if A's property is greater than or equal quantity but not B.
     * Returns 1 if B's property is greater than or equal quantity but not A.
     * Else returns 0.
     */
    protected function ceilComparison(UnitCandidate $a, UnitCandidate $b, string $property, Decimal $quantity): int
    {
        if ($a->{$property} >= $quantity && $b->{$property} < $quantity) {
            return -1;
        }
        if ($a->{$property} < $quantity && $b->{$property} >= $quantity) {
            return 1;
        }

        return 0;
    }

    /**
     * Returns -1 if A's equals quantity but not B.
     * Returns 1 if B's equals quantity but not A.
     * Else returns 0.
     */
    protected function equalComparison(UnitCandidate $a, UnitCandidate $b, string $property, Decimal $quantity): int
    {
        if ($a->{$property} == $quantity && $b->{$property} != $quantity) {
            return -1;
        }
        if ($a->{$property} != $quantity && $b->{$property} == $quantity) {
            return 1;
        }

        return 0;
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
