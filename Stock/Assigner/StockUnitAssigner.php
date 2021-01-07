<?php

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentsInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectInterface;
use Ekyna\Component\Commerce\Stock\Model\StockSubjectModes;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockAssignmentUpdaterInterface;
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
     * @param StockAssignmentManagerInterface $assignmentManager
     * @param StockAssignmentUpdaterInterface $assignmentUpdater
     * @param SaleFactoryInterface            $saleFactory
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        SubjectHelperInterface $subjectHelper,
        StockUnitResolverInterface $unitResolver,
        StockAssignmentManagerInterface $assignmentManager,
        StockAssignmentUpdaterInterface $assignmentUpdater,
        SaleFactoryInterface $saleFactory
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->subjectHelper     = $subjectHelper;
        $this->unitResolver      = $unitResolver;
        $this->assignmentManager = $assignmentManager;
        $this->assignmentUpdater = $assignmentUpdater;
        $this->saleFactory       = $saleFactory;
    }

    /**
     * @inheritdoc
     */
    public function assignSaleItem(SaleItemInterface $item): void
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
    public function applySaleItem(SaleItemInterface $item): void
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
        $this->sortAssignments($assignments);

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
    public function detachSaleItem(SaleItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        /** @var StockAssignmentsInterface $item */

        // Remove stock assignments and schedule events
        foreach ($assignments as $assignment) {
            $this->assignmentUpdater->updateSold($assignment, 0, false);
        }
    }

    /**
     * @inheritdoc
     */
    public function assignShipmentItem(ShipmentItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // TODO sort assignments ?
        // TODO Use packaging format

        $quantity = $item->getQuantity();
        $return   = $item->getShipment()->isReturn();

        if ($item->getShipment()->getState() === ShipmentStates::STATE_PREPARATION) {
            if ($return) {
                // Nothing to do
                return;
            } else {
                // Credit locked quantity
                $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                    return -$this->assignmentUpdater->updateLocked($assignment, $quantity, true);
                };
            }
        } elseif ($return) {
            // Debit shipped quantity
            $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                return $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
            };
        } else {
            // Debit shipped quantity
            $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                return -$this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            };
        }

        // Call on assignments
        foreach ($assignments as $assignment) {
            $quantity += $callable($assignment, $quantity);

            if (0 == $quantity) {
                break;
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
    public function applyShipmentItem(ShipmentItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)
        // TODO Use packaging format

        $shipment = $item->getShipment();

        if (!ShipmentStates::isStockableState($shipment, true)) {
            throw new LogicException("Shipment must be in a stockable state.");
        }

        $return     = $shipment->isReturn();
        $quantityCs = $this->persistenceHelper->getChangeSet($item, 'quantity');

        $quantity   = 0;
        $callable = null;

        // If shipment state changed
        if (!empty($stateCs = $this->persistenceHelper->getChangeSet($shipment, 'state'))) {
            // Old quantity
            $quantity = !empty($quantityCs) ? $quantityCs[0] : $item->getQuantity();

            if (ShipmentStates::hasChangedFromPreparation($stateCs, true)) {
                if ($return) {
                    // Nothing to do
                    return; // TODO Really ?
                } else {
                    // Debit locked quantity
                    $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                        return $this->assignmentUpdater->updateLocked($assignment, -$quantity, true);
                    };
                }
            } elseif (ShipmentStates::hasChangedToPreparation($stateCs, true)) {
                if ($return) {
                    // Credit shipped quantity
                    $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                        return -$this->assignmentUpdater->updateShipped($assignment, +$quantity, true);
                    };
                } else {
                    // Debit shipped quantity
                    $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                        return $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
                    };
                }
            } else {
                throw new LogicException("Unexpected shipment state change.");
            }

            // Call on assignments
            foreach ($assignments as $assignment) {
                $quantity += $callable($assignment, $quantity);

                if (0 == $quantity) {
                    break;
                }
            }

            // New quantity
            $quantity = !empty($quantityCs) ? $quantityCs[1] : $item->getQuantity();
        } // If quantity change
        elseif (!empty($quantityCs)) {
            $quantity = $quantityCs[1] - $quantityCs[0];
        }

        // Abort if zero quantity changed
        if (0 == $quantity) {
            return;
        }

        // Update assignments
        if (ShipmentStates::STATE_PREPARATION === $shipment->getState()) {
            if ($return) {
                // Nothing to do
                return;
            } else {
                // Credit locked quantity
                $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                    return -$this->assignmentUpdater->updateLocked($assignment, $quantity, true);
                };
            }
        } elseif ($return) {
            // Debit shipped quantity
            $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                return $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
            };
        } else {
            // Credit shipped quantity
            $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                return -$this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            };
        }

        // Call on assignments
        foreach ($assignments as $assignment) {
            $quantity += $callable($assignment, $quantity);

            if (0 == $quantity) {
                break;
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
    public function detachShipmentItem(ShipmentItemInterface $item): void
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
        if ($this->persistenceHelper->isChanged($shipment, 'state')) {
            $state = $this->persistenceHelper->getChangeSet($shipment, 'state')[0];
        } else {
            $state = $shipment->getState();
        }

        if (ShipmentStates::STATE_PREPARATION === $state) {
            if ($return) {
                // Nothing to do
                return;
            } else {
                // Debit locked quantity
                $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                    return $this->assignmentUpdater->updateLocked($assignment, -$quantity, true);
                };
            }
        } elseif ($return) {
            // Credit shipped quantity
            $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                return -$this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            };
        } else {
            // Debit shipped quantity
            $callable = function (StockAssignmentInterface $assignment, float $quantity): float {
                return $this->assignmentUpdater->updateShipped($assignment, -$quantity, true);
            };
        }

        // Call on assignments
        foreach ($assignments as $assignment) {
            $quantity += $callable($assignment, $quantity);

            if (0 == $quantity) {
                break;
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
    public function assignInvoiceLine(InvoiceLineInterface $line): void
    {
        $invoice = $line->getInvoice();

        // Abort if not credit
        if (!$invoice->isCredit()) {
            return;
        }

        // Abort if stock ignored
        if ($invoice->isIgnoreStock()) {
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
    public function applyInvoiceLine(InvoiceLineInterface $line): void
    {
        $invoice = $line->getInvoice();

        // Abort if not credit
        if (!$invoice->isCredit()) {
            return;
        }

        // Abort if not supported
        if (null === $assignments = $this->getAssignments($line)) {
            return;
        }

        // TODO sort assignments ?
        // TODO Use packaging format

        $ignoreStockCS = $this->persistenceHelper->getChangeSet($invoice, 'ignoreStock');
        $quantityCs = $this->persistenceHelper->getChangeSet($line, 'quantity');
        $quantity = $callable = null;

        // If 'ignore stock' has changed
        if ($ignoreStockCS[0] != $ignoreStockCS[1]) {
            if ($ignoreStockCS[0]) {
                // Ignore stock disabled -> Debit sold quantity (use previous quantity)
                $quantity = !empty($quantityCs) ? $quantityCs[0] : $line->getQuantity();
                $callable = function(StockAssignmentInterface $assignment, float $quantity): float {
                    return $this->assignmentUpdater->updateSold($assignment, -$quantity, true);
                };
            } elseif ($ignoreStockCS[1]) {
                // Ignore stock enabled -> Credit sold quantity
                $quantity = $line->getQuantity();
                $callable = function(StockAssignmentInterface $assignment, float $quantity): float {
                    return -$this->assignmentUpdater->updateSold($assignment, +$quantity, true);
                };
            }
        } elseif (!$invoice->isIgnoreStock()) {
            // Ignore stock disabled -> Debit sold quantity (use previous quantity)
            $quantity = !empty($quantityCs) ? $quantityCs[1] - $quantityCs[0] : $line->getQuantity();
            $callable = function(StockAssignmentInterface $assignment, float $quantity): float {
                return $this->assignmentUpdater->updateSold($assignment, -$quantity, true);
            };
        }

        if (!($quantity && $callable)) {
            return;
        }

        // Call on assignments
        foreach ($assignments as $assignment) {
            $quantity += $callable($assignment, $quantity);

            if (0 < $quantity) {
                // Create assignments for remaining quantity
                $this->createAssignmentsForQuantity($line->getSaleItem(), $quantity);

                return;
            }

            if (0 == $quantity) {
                break;
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
    public function detachInvoiceLine(InvoiceLineInterface $line): void
    {
        // Abort if not credit
        if (null === $invoice = $line->getInvoice()) {
            $invoice = $this->persistenceHelper->getChangeSet($line, 'invoice')[0];
        }

        // Abort if not credit
        if (!$invoice->isCredit()) {
            return;
        }

        // Abort if stock ignored
        if ($invoice->isIgnoreStock()) {
            return;
        }

        // Abort if not supported
        if (null === $assignments = $this->getAssignments($line)) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)
        // TODO Use packaging format

        // Get previous quantity if it has changed
        if ($this->persistenceHelper->isChanged($line, 'quantity')) {
            $quantity = $this->persistenceHelper->getChangeSet($line, 'quantity')[0];
        } else {
            $quantity = $line->getQuantity();
        }

        // Update assignments
        foreach ($assignments as $assignment) {
            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);
        }

        // Create assignments for remaining quantity
        if (0 < $quantity) {
            $this->createAssignmentsForQuantity($line->getSaleItem(), $quantity);

            return;
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
    public function supportsAssignment(SaleItemInterface $item): bool
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
    protected function getAssignments($item): ?array
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

        /** @var StockAssignmentsInterface $item */
        return $item->getStockAssignments()->toArray();
    }

    /**
     * Creates the sale item assignments for the given quantity.
     *
     * @param SaleItemInterface $item
     * @param float             $quantity
     *
     * @throws StockLogicException If assignment creation fails.
     */
    protected function createAssignmentsForQuantity(SaleItemInterface $item, float $quantity): void
    {
        if (0 >= $quantity) {
            return;
        }

        // Find enough available stock units
        $stockUnits = $this->unitResolver->findAssignable($item);

        $this->sortStockUnits($stockUnits);

        foreach ($stockUnits as $stockUnit) {
            // TODO Look for new assignment that could be used

            $assignment = $this->assignmentManager->create($item, $stockUnit);

            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);

            if (0 == $quantity) {
                return;
            }
        }

        // Remaining quantity
        if (0 < $quantity) {
            $stockUnit = $this->unitResolver->createBySubjectRelative($item);

            $assignment = $this->assignmentManager->create($item, $stockUnit);

            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);
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
    protected function resolveSoldDeltaQuantity(SaleItemInterface $item): float
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
     * Sorts the stock assignments.
     *
     * @param array $assignments
     */
    protected function sortAssignments(array &$assignments): void
    {
        usort($assignments, function (StockAssignmentInterface $a1, StockAssignmentInterface $a2) {
            $u1 = $a1->getStockUnit();
            $u2 = $a2->getStockUnit();

            return $this->compareStockUnit($u1, $u2);
        });
    }

    /**
     * Sorts the stock units.
     *
     * @param array $units
     */
    protected function sortStockUnits(array &$units): void
    {
        usort($units, [$this, 'compareStockUnit']);
    }

    /**
     * Sorts the stock units for credit case (sold quantity).
     *
     * @param StockUnitInterface $u1
     * @param StockUnitInterface $u2
     *
     * @return int
     */
    protected function compareStockUnit(StockUnitInterface $u1, StockUnitInterface $u2): int
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
    protected function compareStockUnitByPrice(StockUnitInterface $u1, StockUnitInterface $u2): int
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
    protected function compareStockUnitByEda(StockUnitInterface $u1, StockUnitInterface $u2): int
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
