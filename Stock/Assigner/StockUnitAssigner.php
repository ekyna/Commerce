<?php

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
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
     * @var StockAssignmentManagerInterface
     */
    protected $assignmentManager;

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
     * @param StockAssignmentManagerInterface $assignmentManager
     * @param StockAssignmentUpdaterInterface $assignmentUpdater
     * @param SaleFactoryInterface            $saleFactory
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        SubjectHelperInterface $subjectHelper,
        StockUnitResolverInterface $unitResolver,
        StockUnitUpdaterInterface $unitUpdater,
        StockAssignmentManagerInterface $assignmentManager,
        StockAssignmentUpdaterInterface $assignmentUpdater,
        SaleFactoryInterface $saleFactory
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->subjectHelper     = $subjectHelper;
        $this->unitResolver      = $unitResolver;
        $this->unitUpdater       = $unitUpdater;
        $this->assignmentManager = $assignmentManager;
        $this->assignmentUpdater = $assignmentUpdater;
        $this->saleFactory       = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function assignSaleItem(SaleItemInterface $item)
    {
        // Abort if not supported
        if (!$this->supportsAssignment($item)) {
            return;
        }

        // Don't assign twice
        if (!empty($this->getAssignments($item))) {
            throw new StockLogicException(sprintf(
                'Item "%s" is already assigned.',
                $item->getDesignation()
            ));
        }

        // Create assignments
        $this->createAssignmentsForQuantity($item, $item->getTotalQuantity());
    }

    /**
     * @inheritdoc
     */
    public function applySaleItem(SaleItemInterface $item)
    {
        // Abort if not supported
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
            throw new StockLogicException(sprintf(
                'Failed to dispatch sale item "%s" changed quantity debit over assigned stock units.',
                $item->getDesignation()
            ));
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
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        /** @var StockAssignmentsInterface $item */

        // Remove stock assignments and schedule events
        foreach ($assignments as $assignment) {
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
        // TODO Use packaging format

        $quantity = $item->getQuantity();
        $return   = $item->getShipment()->isReturn();

        foreach ($assignments as $assignment) {
            if ($return) {
                $quantity += $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
            } else {
                $quantity -= $this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            }
        }

        // Remaining quantity
        if (0 != $quantity) {
            throw new StockLogicException(sprintf(
                'Failed to assign shipment item "%s".',
                $item->getSaleItem()->getDesignation()
            ));
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
        [$old, $new] = $this->persistenceHelper->getChangeSet($item, 'quantity');
        if (0 == $quantity = $new - $old) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)
        // TODO Use packaging format

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
            throw new StockLogicException(sprintf(
                'Failed to apply shipment item "%s".',
                $item->getSaleItem()->getDesignation()
            ));
        }
    }

    /**
     * @inheritdoc
     */
    public function detachShipmentItem(ShipmentItemInterface $item)
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)
        // TODO Use packaging format

        // Get previous quantity if it has changed
        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            $quantity = $this->persistenceHelper->getChangeSet($item, 'quantity')[0];
        } else {
            $quantity = $item->getQuantity();
        }

        // Get shipment from change set if needed
        if (null === $shipment = $item->getShipment()) {
            $shipment = $this->persistenceHelper->getChangeSet($item, 'shipment')[0];
        }

        $return = $shipment->isReturn();

        // Update assignments
        foreach ($assignments as $assignment) {
            if ($return) {
                $quantity -= $this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            } else {
                $quantity += $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
            }
        }

        // Remaining quantity
        if (0 != $quantity) {
            throw new StockLogicException(sprintf(
                'Failed to detach shipment item "%s".',
                $item->getSaleItem()->getDesignation()
            ));
        }
    }

    /**
     * @inheritdoc
     */
    public function assignInvoiceLine(InvoiceLineInterface $line)
    {
        // Abort if not credit
        if (!$line->getInvoice()->isCredit()) {
            return;
        }

        // Abort if not supported
        if (null === $assignments = $this->getAssignments($line)) {
            return;
        }

        // TODO sort assignments ?
        // TODO Use packaging format

        $quantity = $line->getQuantity();

        foreach ($assignments as $assignment) {
            $quantity += $this->assignmentUpdater->updateSold($assignment, -$quantity, true);
        }

        // Remaining quantity
        if (0 != $quantity) {
            throw new StockLogicException(sprintf(
                'Failed to assign invoice line "%s".',
                $line->getDesignation()
            ));
        }
    }

    /**
     * @inheritdoc
     */
    public function applyInvoiceLine(InvoiceLineInterface $line)
    {
        // Abort if not credit
        if (!$line->getInvoice()->isCredit()) {
            return;
        }

        // Abort if not supported
        if (null === $assignments = $this->getAssignments($line)) {
            return;
        }

        // Resolve quantity change
        if (!$this->persistenceHelper->isChanged($line, 'quantity')) {
            return;
        }
        [$old, $new] = $this->persistenceHelper->getChangeSet($line, 'quantity');
        if (0 == $quantity = $new - $old) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)
        // TODO Use packaging format

        // Update assignments
        foreach ($assignments as $assignment) {
            $quantity += $this->assignmentUpdater->updateSold($assignment, -$quantity, true);

            if (0 == $quantity) {
                return;
            }
        }

        // Remaining quantity
        if (0 != $quantity) {
            throw new StockLogicException(sprintf(
                'Failed to apply invoice line "%s".',
                $line->getDesignation()
            ));
        }
    }

    /**
     * @inheritdoc
     */
    public function detachInvoiceLine(InvoiceLineInterface $line)
    {
        // Abort if not credit
        if (null === $invoice = $line->getInvoice()) {
            $invoice = $this->persistenceHelper->getChangeSet($line, 'invoice')[0];
        }
        if (!$invoice->isCredit()) {
            return;
        }

        // Abort if not supported
        if (null === $assignments = $this->getAssignments($line)) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)
        // TODO Use packaging format

        $quantity = $line->getQuantity();

        // Update assignments
        foreach ($assignments as $assignment) {
            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);
        }

        // Remaining quantity
        if (0 != $quantity) {
            throw new StockLogicException(sprintf(
                'Failed to detach invoice line "%s".',
                $line->getDesignation()
            ));
        }
    }

    /**
     * @inheritdoc
     */
    public function supportsAssignment(SaleItemInterface $item)
    {
        // TODO Check if sale is in stockable state

        if (!$item instanceof StockAssignmentsInterface) {
            return false;
        }

        if (null === $subject = $this->subjectHelper->resolve($item, false)) {
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
            if (!$item = $item->getSaleItem()) {
                return null;
            }
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

        $this->assignmentManager->remove($assignment);
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
            $assignment = $this->assignmentManager->create($item, $stockUnit);

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
            throw new StockLogicException(sprintf(
                'Failed to create assignments for item "%s".',
                $item->getDesignation()
            ));
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

        // Own item quantity changes
        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            [$old, $new] = $this->persistenceHelper->getChangeSet($item, 'quantity');
        }

        // Parent items quantity changes
        $parent = $item;
        while (null !== $parent = $parent->getParent()) {
            if ($this->persistenceHelper->isChanged($parent, 'quantity')) {
                [$parentOld, $parentNew] = $this->persistenceHelper->getChangeSet($parent, 'quantity');
            } else {
                $parentOld = $parentNew = $parent->getQuantity();
            }
            $old *= $parentOld;
            $new *= $parentNew;
        }

        // Sale released change
        $sale       = $item->getSale();
        $shippedOld = $shippedNew = 0;
        $f          = $t = false;
        if ($this->persistenceHelper->isChanged($sale, 'released')) {
            [$f, $t] = $this->persistenceHelper->getChangeSet($sale, 'released');
        } elseif ($item->getSale()->isReleased()) {
            $f = $t = true;
        }
        if ($f || $t) {
            /** @var StockAssignmentsInterface $item */
            foreach ($item->getStockAssignments() as $assignment) {
                if ($this->persistenceHelper->isChanged($assignment, 'shippedQuantity')) {
                    [$o, $n] = $this->persistenceHelper->getChangeSet($assignment, 'shippedQuantity');
                } else {
                    $o = $n = $assignment->getShippedQuantity();
                }
                if ($f) {
                    $shippedOld += $o;
                }
                if ($t) {
                    $shippedNew += $n;
                }
            }

            if ($f) {
                $old = min($old, $shippedOld);
            }
            if ($t) {
                $new = min($new, $shippedNew);
            }
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
        usort($assignments, function (StockAssignmentInterface $a1, StockAssignmentInterface $a2) {
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
        usort($stockUnits, [$this, 'compareStockUnit']);

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

        $u1Result = $u1->getReceivedQuantity() + $u1->getAdjustedQuantity();
        $u2Result = $u2->getReceivedQuantity() + $u2->getAdjustedQuantity();

        // Prefer stock units with received/adjusted quantities
        if (0 < $u1Result && 0 == $u2Result) {
            return -1;
        } elseif (0 == $u1Result && 0 < $u2Result) {
            return 1;
        } elseif (0 < $u1Result && 0 < $u2Result) {
            // If both have received quantities, prefer cheapest
            if (0 != $result = $this->compareStockUnitByPrice($u1, $u2)) {
                return $result;
            }
        }

        $u1Result = $u1->getOrderedQuantity() + $u1->getAdjustedQuantity();
        $u2Result = $u2->getOrderedQuantity() + $u2->getAdjustedQuantity();

        // Prefer stock units with ordered quantities
        if (0 < $u1Result && 0 == $u2Result) {
            return -1;
        } elseif (0 == $u1Result && 0 < $u2Result) {
            return 1;
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
