<?php

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitAssigner
 * @package Ekyna\Component\Commerce\Stock\Assigner
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitAssigner implements StockUnitAssignerInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var SubjectHelperInterface
     */
    protected $subjectHelper;

    /**
     * @var StockUnitResolverInterface
     */
    protected $stockUnitResolver;

    /**
     * @var StockUnitUpdaterInterface
     */
    protected $stockUnitUpdater;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param SubjectHelperInterface     $subjectHelper
     * @param StockUnitResolverInterface $stockUnitResolver
     * @param StockUnitUpdaterInterface  $stockUnitUpdater
     * @param SaleFactoryInterface       $saleFactory
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        SubjectHelperInterface $subjectHelper,
        StockUnitResolverInterface $stockUnitResolver,
        StockUnitUpdaterInterface $stockUnitUpdater,
        SaleFactoryInterface $saleFactory
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->subjectHelper = $subjectHelper;
        $this->stockUnitResolver = $stockUnitResolver;
        $this->stockUnitUpdater = $stockUnitUpdater;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function createAssignments(SaleItemInterface $item)
    {
        if (!$this->supportsAssignment($item)) {
            return;
        }

        $this->createAssignmentsForQuantity($item, $item->getTotalQuantity());
    }

    /**
     * @inheritdoc
     */
    public function updateAssignments(SaleItemInterface $item)
    {
        if (!$this->supportsAssignment($item)) {
            return;
        }

        if (0 == $deltaQuantity = $this->resolveDeltaQuantity($item)) {
            return;
        }

        /** @var StockAssignmentsInterface $item */

        // Determine on which stock units the reserved quantity change should be dispatched
        $assignments = $item->getStockAssignments()->toArray();
        uasort($assignments, function (StockAssignmentInterface $a1, StockAssignmentInterface $a2) {
            $u1 = $a1->getStockUnit();
            $u2 = $a2->getStockUnit();

            return $this->sortStockUnits($u1, $u2);
        });

        // Debit case : reverse the sorted assignments
        if (0 > $deltaQuantity) {
            $assignments = array_reverse($assignments);
        }

        /** @var StockAssignmentInterface $assignment */
        foreach ($assignments as $assignment) {
            $stockUnit = $assignment->getStockUnit();

            $delta = null;
            // Debit case
            if (0 > $deltaQuantity) {
                // If we're about to debit more than the assignment quantity, just remove the assignment
                if ($assignment->getQuantity() <= abs($deltaQuantity)) {
                    $item->removeStockAssignment($assignment);
                    $this->removeAssignment($assignment);

                    $deltaQuantity += $assignment->getQuantity();
                    continue;
                }

                // Reserved quantity can't be lower than shipped
                if (0 < $stockUnit->getShippedQuantity() && $stockUnit->getShippedQuantity() <= abs($deltaQuantity)) {
                    $delta = -$stockUnit->getShippedQuantity();
                }
            } // Credit case
            elseif (0 < $deltaQuantity) {
                // Reserved quantity can't be greater than ordered quantity
                if (0 < $stockUnit->getOrderedQuantity()) {
                    $assignableQuantity = $stockUnit->getOrderedQuantity() - $stockUnit->getReservedQuantity();
                    if (0 < $assignableQuantity && $assignableQuantity < $deltaQuantity) {
                        $delta = $assignableQuantity;
                    } else {
                        continue;
                    }
                }
            }
            if (null === $delta) {
                $delta = $deltaQuantity;
            }

            // Apply delta to stock unit
            $this->stockUnitUpdater->updateReserved($stockUnit, $delta, true);

            // Apply delta to assignment
            $assignment->setQuantity($assignment->getQuantity() + $delta);
            $this->persistenceHelper->persistAndRecompute($assignment);

            $deltaQuantity -= $delta;
            if (0 == $deltaQuantity) {
                return;
            }
        }

        // Remaining debit
        if (0 > $deltaQuantity) {
            throw new InvalidArgumentException(
                'Failed to dispatch sale item changed quantity debit over assigned stock units.'
            );
        }

        // Remaining credit
        if (0 < $deltaQuantity) {
            $this->createAssignmentsForQuantity($item, $deltaQuantity);
        }
    }

    /**
     * @inheritdoc
     */
    public function removeAssignments(SaleItemInterface $item)
    {
        if (!$this->supportsAssignment($item)) {
            return;
        }

        /** @var StockAssignmentsInterface $item */

        // Remove stock assignments and schedule events
        foreach ($item->getStockAssignments() as $assignment) {
            $item->removeStockAssignment($assignment);
            $this->removeAssignment($assignment);
        }
    }

    /**
     * Returns whether or not the given item supports assignments.
     *
     * @param SaleItemInterface $item
     *
     * @return bool
     */
    protected function supportsAssignment(SaleItemInterface $item)
    {
        if (!$item instanceof StockAssignmentsInterface) {
            return false;
        }

        if (null === $subject = $this->subjectHelper->resolve($item)) {
            return false;
        }

        if (!$subject instanceof StockSubjectInterface) {
            return false;
        }

        if ($subject->getStockMode() != StockSubjectModes::MODE_ENABLED) {
            return false;
        }

        return true;
    }

    /**
     * Removes a single assignment.
     *
     * @param StockAssignmentInterface $assignment
     */
    protected function removeAssignment(StockAssignmentInterface $assignment)
    {
        $this->stockUnitUpdater->updateReserved($assignment->getStockUnit(), -$assignment->getQuantity(), true);

        $this->persistenceHelper->remove($assignment);
    }

    /**
     * Creates the sale item assignments for the given quantity.
     *
     * @param SaleItemInterface $item
     * @param float             $quantity
     */
    protected function createAssignmentsForQuantity(SaleItemInterface $item, $quantity)
    {
        // Find enough available stock units

        // TODO Stock units created during the flush event are not available for repository methods.
        // We need to cache them to use them right here.

        $stockUnits = $this->stockUnitResolver->findAssignable($item);
        uasort($stockUnits, [$this, 'sortStockUnits']);

        foreach ($stockUnits as $stockUnit) {
            $assignment = $this->saleFactory->createStockAssignmentForItem($item);

            $delta = null;
            if (0 < $stockUnit->getOrderedQuantity()) {
                $assignableQuantity = $stockUnit->getOrderedQuantity() - $stockUnit->getReservedQuantity();
                if (0 == $assignableQuantity) {
                    continue;
                }
                if ($assignableQuantity < $quantity) {
                    $delta = $assignableQuantity;
                }
            }
            if (null === $delta) {
                $delta = $quantity;
            }

            $this->stockUnitUpdater->updateReserved($stockUnit, $delta, true);

            $assignment
                ->setSaleItem($item)
                ->setStockUnit($stockUnit)
                ->setQuantity($delta);

            $this->persistenceHelper->persistAndRecompute($assignment);

            $quantity -= $delta;

            if (0 == $quantity) {
                return;
            }
        }

        // Remaining quantity
        if (0 < $quantity) {
            $stockUnit = $this->stockUnitResolver->createBySubjectRelative($item);
            $this->stockUnitUpdater->updateReserved($stockUnit, $quantity, false);

            $assignment = $this->saleFactory->createStockAssignmentForItem($item);
            $assignment
                ->setSaleItem($item)
                ->setStockUnit($stockUnit)
                ->setQuantity($quantity);

            $this->persistenceHelper->persistAndRecompute($assignment);
        }
    }

    /**
     * Resolves the assignments update's delta quantity.
     *
     * @param SaleItemInterface $item
     *
     * @return float
     */
    protected function resolveDeltaQuantity(SaleItemInterface $item)
    {
        $old = $new = $item->getQuantity();

        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($item)['quantity'];
        }

        $parent = $item;
        while (null !== $parent = $parent->getParent()) {
            if ($this->persistenceHelper->isChanged($parent, 'quantity')) {
                list($parentOld, $parentNew) = $this->persistenceHelper->getChangeSet($parent)['quantity'];
            } else {
                $parentOld = $parentNew = $item->getQuantity();
            }
            $old *= $parentOld;
            $new *= $parentNew;
        }

        return $new - $old;
    }

    /**
     * Sorts the stock units for credit case (reserved quantity).
     *
     * @param StockUnitInterface $u1
     * @param StockUnitInterface $u2
     *
     * @return int
     */
    protected function sortStockUnits(StockUnitInterface $u1, StockUnitInterface $u2)
    {
        // TODO Review this code / make it configurable

        /**
         * @return int
         */
        $sortByPrice = function () use ($u1, $u2) {
            $u1HasPrice = 0 < $u1->getNetPrice();
            $u2HasPrice = 0 < $u2->getNetPrice();

            if (!$u1HasPrice && $u2HasPrice) {
                return 1;
            }
            if ($u1HasPrice && !$u2HasPrice) {
                return -1;
            }
            if ($u1->getNetPrice() != $u2->getNetPrice()) {
                return $u1->getNetPrice() > $u2->getNetPrice() ? 1 : -1;
            }

            return 0;
        };

        /**
         * @return int
         */
        $sortByEda = function () use ($u1, $u2) {
            $u1HasEda = null !== $u1->getEstimatedDateOfArrival();
            $u2HasEda = null !== $u2->getEstimatedDateOfArrival();

            if (!$u1HasEda && $u2HasEda) {
                return 1;
            }
            if ($u1HasEda && !$u2HasEda) {
                return -1;
            }
            if ($u1->getEstimatedDateOfArrival() != $u2->getEstimatedDateOfArrival()) {
                return $u1->getEstimatedDateOfArrival() > $u2->getEstimatedDateOfArrival() ? 1 : -1;
            }

            return 0;
        };

        // Sorting is made for credit case

        // Prefer stock units with delivered quantities
        if (0 < $u1->getDeliveredQuantity() && 0 == $u2->getDeliveredQuantity()) {
            return -1;
        } elseif (0 == $u1->getDeliveredQuantity() && 0 < $u2->getDeliveredQuantity()) {
            return 1;
        } elseif (0 < $u1->getDeliveredQuantity() && 0 < $u2->getDeliveredQuantity()) {
            // If both have delivered quantities, prefer cheapest
            if (0 != $result = $sortByPrice($u1, $u2)) {
                return $result;
            }
        }

        // Prefer stock units with ordered quantities
        if (0 < $u1->getOrderedQuantity() && 0 == $u2->getOrderedQuantity()) {
            return -1;
        } elseif (0 == $u1->getOrderedQuantity() && 0 < $u2->getOrderedQuantity()) {
            return 1;
        } elseif (0 < $u1->getOrderedQuantity() && 0 < $u2->getOrderedQuantity()) {
            // If both have ordered quantities, prefer closest eda
            if (0 != $result = $sortByEda($u1, $u2)) {
                return $result;
            }
        }

        // By eta DESC
        /*$now = new \DateTime();
        // Positive if future / Negative if past
        $u1Diff = (null !== $date = $u1->getEstimatedDateOfArrival())
                ? $now->diff($date)->format('%R%a')
                : +9999;
        $u2Diff = (null !== $date = $u2->getEstimatedDateOfArrival())
                ? $now->diff($date)->format('%R%a')
                : +9999;
        if (abs($u1Diff) < 30 && abs($u2Diff) < 30) {

        }*/

        // By eta DESC
        if (0 != $result = $sortByEda($u1, $u2)) {
            return $result;
        }

        // By price ASC
        if (0 != $result = $sortByPrice($u1, $u2)) {
            return $result;
        }

        return 0;
    }
}
