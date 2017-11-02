<?php

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockAssignmentUpdaterInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitAssigner
 * @package Ekyna\Component\Commerce\Stock\Assigner
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * This class is responsible for assigning sale items to stock units.
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
    protected $unitResolver;

    /**
     * @var StockUnitUpdaterInterface
     */
    protected $unitUpdater;

    /**
     * @var StockAssignmentUpdaterInterface
     */
    protected $assignmentUpdater;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface      $persistenceHelper
     * @param SubjectHelperInterface          $subjectHelper
     * @param StockUnitResolverInterface      $unitResolver
     * @param StockUnitUpdaterInterface       $unitUpdater
     * @param StockAssignmentUpdaterInterface $assignmentUpdater
     * @param SaleFactoryInterface            $saleFactory
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        SubjectHelperInterface $subjectHelper,
        StockUnitResolverInterface $unitResolver,
        StockUnitUpdaterInterface $unitUpdater,
        StockAssignmentUpdaterInterface $assignmentUpdater,
        SaleFactoryInterface $saleFactory
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->subjectHelper = $subjectHelper;
        $this->unitResolver = $unitResolver;
        $this->unitUpdater = $unitUpdater;
        $this->assignmentUpdater = $assignmentUpdater;
        $this->saleFactory = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function assignSaleItem(SaleItemInterface $item)
    {
        if (!$this->supportsAssignment($item)) {
            return;
        }

        $this->createAssignmentsForQuantity($item, $item->getTotalQuantity());
    }

    /**
     * @inheritdoc
     */
    public function applySaleItem(SaleItemInterface $item)
    {
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        if (0 == $quantity = $this->resolveSoldDeltaQuantity($item)) {
            return;
        }

        /** @var StockAssignmentsInterface $item */

        // Determine on which stock units the sold quantity change should be dispatched
        $assignments = $this->sortAssignments($assignments);

        // Debit case : reverse the sorted assignments
        if (0 > $quantity) {
            $assignments = array_reverse($assignments);
        }

        /** @var StockAssignmentInterface $assignment */
        foreach ($assignments as $assignment) {
            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);
            if (0 == $quantity) {
                return;
            }
        }

        // Remaining debit
        if (0 > $quantity) {
            throw new StockLogicException(
                'Failed to dispatch sale item changed quantity debit over assigned stock units.'
            );
        }

        // Remaining credit
        if (0 < $quantity) {
            $this->createAssignmentsForQuantity($item, $quantity);
        }
    }

    /**
     * @inheritdoc
     */
    public function detachSaleItem(SaleItemInterface $item)
    {
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        /** @var StockAssignmentsInterface $item */

        // Remove stock assignments and schedule events
        foreach ($assignments as $assignment) {
            $item->removeStockAssignment($assignment);
            $this->removeAssignment($assignment);
        }
    }

    /**
     * @inheritdoc
     */
    public function assignShipmentItem(ShipmentItemInterface $item)
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // TODO sort assignments ?

        $quantity = $item->getQuantity();
        $return = $item->getShipment()->isReturn();

        // TODO Use packaging format

        foreach ($assignments as $assignment) {
            if ($return) {
                $quantity += $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
            } else {
                $quantity -= $this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            }
            // TODO Should be relative ? (multiple shipment items may point to the same sale item)
            //$quantity -= $this->assignmentUpdater->updateShipped($assignment, $quantity, false);
        }

        // Remaining quantity
        if (0 != $quantity) {
            throw new StockLogicException('Failed to assign shipment item.');
        }
    }

    /**
     * @inheritdoc
     */
    public function applyShipmentItem(ShipmentItemInterface $item)
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // Resolve quantity change
        if (!$this->persistenceHelper->isChanged($item, 'quantity')) {
            return;
        }
        list($old, $new) = $this->persistenceHelper->getChangeSet($item, 'quantity');
        if (0 == $quantity = $new - $old) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)

        $return = $item->getShipment()->isReturn();

        // Update assignments
        foreach ($assignments as $assignment) {
            if ($return) {
                $quantity += $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
            } else {
                $quantity -= $this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            }

            if (0 == $quantity) {
                return;
            }
        }

        // Remaining quantity
        if (0 != $quantity) {
            throw new StockLogicException('Failed to assign shipment item.');
        }
    }

    /**
     * @inheritdoc
     */
    public function detachShipmentItem(ShipmentItemInterface $item)
    {
        // Abort if not supporter
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)

        // TODO Use packaging format
        $quantity = $item->getQuantity();
        $return = $item->getShipment()->isReturn();

        // Update assignments
        foreach ($assignments as $assignment) {
            if ($return) {
                $quantity -= $this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            } else {
                $quantity += $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
            }
            // TODO Should be relative ? (multiple shipment items may point to the same sale item)
            //$result += $this->assignmentUpdater->updateShipped($assignment, 0, false);
        }

        // Remaining quantity
        if (0 != $quantity) {
            throw new StockLogicException('Failed to detach shipment item.');
        }
    }

    /**
     * Returns whether or not the given item supports assignments.
     *
     * @param mixed $item
     *
     * @return bool
     */
    protected function supportsAssignment($item)
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

        if ($subject->isStockCompound()) {
            return false;
        }

        if ($subject->getStockMode() === StockSubjectModes::MODE_DISABLED) {
            return false;
        }

        return true;
    }

    /**
     * Returns the item's stock assignments, or null if not supported.
     *
     * @param mixed $item
     *
     * @return null|StockAssignmentInterface[]
     */
    protected function getAssignments($item)
    {
        if ($item instanceof ShipmentItemInterface) {
            $item = $item->getSaleItem();
        } elseif ($item instanceof InvoiceLineInterface) {
            $item = $item->getSaleItem();
        }

        if (!$this->supportsAssignment($item)) {
            return null;
        }

        return $item->getStockAssignments()->toArray();
    }

    /**
     * Removes a single assignment.
     *
     * @param StockAssignmentInterface $assignment
     */
    protected function removeAssignment(StockAssignmentInterface $assignment)
    {
        $this->unitUpdater->updateSold($assignment->getStockUnit(), -$assignment->getSoldQuantity(), true);

        $this->persistenceHelper->remove($assignment);
    }

    /**
     * Creates the sale item assignments for the given quantity.
     *
     * @param SaleItemInterface $item
     * @param float             $quantity
     *
     * @throws StockLogicException If assignment creation fails.
     */
    protected function createAssignmentsForQuantity(SaleItemInterface $item, $quantity)
    {
        if (0 >= $quantity) {
            return;
        }

        // Find enough available stock units
        $stockUnits = $this->sortStockUnits($this->unitResolver->findAssignable($item));

        foreach ($stockUnits as $stockUnit) {
            $assignment = $this->saleFactory->createStockAssignmentForItem($item);
            $assignment
                ->setSaleItem($item)
                ->setStockUnit($stockUnit);

            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity);

            if (0 == $quantity) {
                return;
            }
        }

        // Remaining quantity
        if (0 < $quantity) {
            $stockUnit = $this->unitResolver->createBySubjectRelative($item);

            $assignment = $this->saleFactory->createStockAssignmentForItem($item);
            $assignment
                ->setSaleItem($item)
                ->setStockUnit($stockUnit);

            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity);
        }

        if (0 < $quantity) {
            throw new StockLogicException('Failed to create assignments.');
        }
    }

    /**
     * Resolves the assignments update's delta quantity.
     *
     * @param SaleItemInterface $item
     *
     * @return float
     */
    protected function resolveSoldDeltaQuantity(SaleItemInterface $item)
    {
        $old = $new = $item->getQuantity();

        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            list($old, $new) = $this->persistenceHelper->getChangeSet($item, 'quantity');
        }

        $parent = $item;
        while (null !== $parent = $parent->getParent()) {
            if ($this->persistenceHelper->isChanged($parent, 'quantity')) {
                list($parentOld, $parentNew) = $this->persistenceHelper->getChangeSet($parent, 'quantity');
            } else {
                $parentOld = $parentNew = $item->getQuantity();
            }
            $old *= $parentOld;
            $new *= $parentNew;
        }

        return $new - $old;
    }

    /**
     * Sorts assignments.
     *
     * @param array $assignments
     *
     * @return array
     */
    protected function sortAssignments(array $assignments)
    {
        uasort($assignments, function (StockAssignmentInterface $a1, StockAssignmentInterface $a2) {
            $u1 = $a1->getStockUnit();
            $u2 = $a2->getStockUnit();

            return $this->compareStockUnit($u1, $u2);
        });

        return $assignments;
    }

    /**
     * Sorts the stock units.
     *
     * @param array $stockUnits
     *
     * @return array
     */
    protected function sortStockUnits(array $stockUnits)
    {
        uasort($stockUnits, [$this, 'compareStockUnit']);

        return $stockUnits;
    }

    /**
     * Sorts the stock units for credit case (sold quantity).
     *
     * @param StockUnitInterface $u1
     * @param StockUnitInterface $u2
     *
     * @return int
     */
    protected function compareStockUnit(StockUnitInterface $u1, StockUnitInterface $u2)
    {
        // TODO Review this code / make it configurable

        // Sorting is made for credit case

        // Prefer stock units with received quantities
        if (0 < $u1->getReceivedQuantity() && 0 == $u2->getReceivedQuantity()) {
            return -1;
        } elseif (0 == $u1->getReceivedQuantity() && 0 < $u2->getReceivedQuantity()) {
            return 1;
        } elseif (0 < $u1->getReceivedQuantity() && 0 < $u2->getReceivedQuantity()) {
            // If both have received quantities, prefer cheapest
            if (0 != $result = $this->compareStockUnitByPrice($u1, $u2)) {
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
            if (0 != $result = $this->compareStockUnitByEda($u1, $u2)) {
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
        if (0 != $result = $this->compareStockUnitByEda($u1, $u2)) {
            return $result;
        }

        // By price ASC
        if (0 != $result = $this->compareStockUnitByPrice($u1, $u2)) {
            return $result;
        }

        return 0;
    }

    /**
     * Compares the units regarding to their price.
     *
     * @param StockUnitInterface $u1
     * @param StockUnitInterface $u2
     *
     * @return int
     */
    protected function compareStockUnitByPrice(StockUnitInterface $u1, StockUnitInterface $u2)
    {
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
    }

    /**
     * Compares the units regarding to their estimated date of arrival.
     *
     * @param StockUnitInterface $u1
     * @param StockUnitInterface $u2
     *
     * @return int
     */
    protected function compareStockUnitByEda(StockUnitInterface $u1, StockUnitInterface $u2)
    {
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
    }
}
