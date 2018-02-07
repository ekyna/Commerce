<?php

namespace Ekyna\Component\Commerce\Stock\Prioritizer;

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
    private $unitResolver;


    /**
     * Constructor.
     *
     * @param StockUnitResolverInterface $unitResolver
     */
    public function __construct(StockUnitResolverInterface $unitResolver)
    {
        $this->unitResolver = $unitResolver;
    }

    /**
     * Finds the best stock unit to move/merge assignment(s) into for the given quantity.
     *
     * @param StockAssignmentInterface $assignment
     * @param float                    $quantity
     *
     * @return UnitCandidate|null
     */
    public function getUnitCandidate(StockAssignmentInterface $assignment, $quantity)
    {
        $subject = $assignment->getStockUnit()->getSubject();

        // Find the subject's ready stock units
        if (empty($units = $this->unitResolver->findReady($subject))) {
            return null;
        };

        $sale = $assignment->getSaleItem()->getSale();

        $candidates = [];

        foreach ($units as $unit) {
            $this->unitResolver->getStockUnitCache()->add($unit);

            $candidate = UnitCandidate::build($unit, $sale);

            if (0 >= $diff = $quantity - $candidate->reservable) {
                // Unit has enough reservable quantity
                return $candidate;
            }

            $candidate->getCombination($diff);

            $candidates[] = $candidate;
        }

        if (empty($candidates)) {
            return null;
        }

        // Sort candidates
        usort($candidates, function (UnitCandidate $a, UnitCandidate $b) use ($quantity) {
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
    private function ceilComparison(UnitCandidate $a, UnitCandidate $b, $property, $quantity)
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
    private function equalComparison(UnitCandidate $a, UnitCandidate $b, $property, $quantity)
    {
        if ($a->{$property} == $quantity && $b->{$property} != $quantity) {
            return -1;
        }
        if ($a->{$property} != $quantity && $b->{$property} == $quantity) {
            return 1;
        }

        return false;
    }

    /**
     * Returns -1 if A's property is greater than
     *
     * @param UnitCandidate $a
     * @param UnitCandidate $b
     * @param string        $property
     *
     * @return bool|int
     */
    private function greaterThanComparison(UnitCandidate $a, UnitCandidate $b, $property)
    {
        if ($a->{$property} > $b->{$property}) {
            return -1;
        }
        if ($a->{$property} < $b->{$property}) {
            return 1;
        }

        return false;
    }
}