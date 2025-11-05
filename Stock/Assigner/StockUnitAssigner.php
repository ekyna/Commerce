<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Assigner;

use Decimal\Decimal;
use Ekyna\Component\Commerce\Common\Helper\FactoryHelperInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceLineInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionInterface;
use Ekyna\Component\Commerce\Manufacture\Model\ProductionItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderInvoiceLineInterface;
use Ekyna\Component\Commerce\Order\Model\OrderItemInterface;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
use Ekyna\Component\Commerce\Order\Resolver\QuantityResolver as OrderQuantityResolver;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignableInterface;
use Ekyna\Component\Commerce\Stock\Model\AssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Updater\StockAssignmentUpdaterInterface;
use Ekyna\Component\Commerce\Subject\SubjectHelperInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use Exception;

use function array_reverse;
use function sprintf;

/**
 * Class StockUnitAssigner
 * @package Ekyna\Component\Commerce\Stock\Assigner
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * This class is responsible for assigning sale items to stock units.
 *
 * TODO Use InvoiceSubjectCalculator to get sold quantity.
 *      Update regarding difference between this result and stock assignments sold quantity sum.
 *
 * TODO Refactor/Split into multiple assigners:
 *          - OrderItemAssigner
 *          - ProductionItemAssigner
 *          - etc
 */
class StockUnitAssigner implements StockUnitAssignerInterface
{
    use AssignmentSupportTrait;

    public function __construct(
        protected readonly PersistenceHelperInterface      $persistenceHelper,
        protected readonly StockUnitResolverInterface      $unitResolver,
        protected readonly StockAssignmentManagerInterface $assignmentManager,
        protected readonly StockAssignmentUpdaterInterface $assignmentUpdater,
        protected readonly FactoryHelperInterface          $factoryHelper,
        SubjectHelperInterface                             $subjectHelper,
    ) {
        $this->subjectHelper = $subjectHelper;
    }

    public function assignOrderItem(OrderItemInterface $item): void
    {
        // Abort if not supported
        if (!$this->supportsAssignment($item)) {
            return;
        }

        // Calculate missing assigned quantity
        $assigned = new Decimal(0);
        foreach ($this->getAssignments($item) as $assignment) {
            $assigned += $assignment->getSoldQuantity();
        }

        // Create assignments
        $this->createAssignmentsForQuantity($item, $item->getTotalQuantity()->sub($assigned));
    }

    public function applyOrderItem(OrderItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        $helper = new OrderQuantityResolver($this->persistenceHelper);
        $quantity = $helper->resolveSoldDelta($item);
        if ($quantity->isZero()) {
            return;
        }

        // Determine on which stock units the sold quantity change should be dispatched
        $this->sortAssignments($assignments);

        // Debit case : reverse the sorted assignments
        if (0 > $quantity) {
            $assignments = array_reverse($assignments);
        }

        /** @var AssignmentInterface $assignment */
        foreach ($assignments as $assignment) {
            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);
            if ($quantity->isZero()) {
                return;
            }
        }

        // Remaining debit
        if (0 > $quantity) {
            throw new StockLogicException(sprintf(
                'Failed to dispatch sale item "%s" changed quantity debit over assigned stock units.',
                $item
            ));
        }

        // Remaining credit
        if (0 < $quantity) {
            $this->createAssignmentsForQuantity($item, $quantity);
        }
    }

    public function detachOrderItem(OrderItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        /** @var AssignableInterface $item */

        // Remove stock assignments and schedule events
        foreach ($assignments as $assignment) {
            $this->assignmentUpdater->updateSold($assignment, new Decimal(0), false);
        }
    }

    public function assignProductionItem(ProductionItemInterface $item): void
    {
        // Abort if not supported
        if (!$this->supportsAssignment($item)) {
            return;
        }

        // Calculate missing assigned quantity
        $assigned = new Decimal(0);
        foreach ($this->getAssignments($item) as $assignment) {
            $assigned += $assignment->getSoldQuantity();
        }

        // Create assignments
        $this->createAssignmentsForQuantity($item, $item->getTotalQuantity()->sub($assigned));
    }

    public function applyProductionItem(ProductionItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        if (empty($itemCs = $this->persistenceHelper->getChangeSet($item, 'quantity'))) {
            $itemCs = [
                0 => $item->getQuantity(),
                1 => $item->getQuantity(),
            ];
        }
        $order = $item->getProductionOrder();
        if (empty($orderCs = $this->persistenceHelper->getChangeSet($item->getProductionOrder(), 'quantity'))) {
            $orderCs = [
                0 => $order->getQuantity(),
                1 => $order->getQuantity(),
            ];
        }

        $quantity = $itemCs[1]
            ->mul($orderCs[1])
            ->rem(
                $itemCs[0]->mul($orderCs[0])
            );

        if ($quantity->isZero()) {
            return;
        }

        // Determine on which stock units the sold quantity change should be dispatched
        $this->sortAssignments($assignments);

        // Debit case : reverse the sorted assignments
        if (0 > $quantity) {
            $assignments = array_reverse($assignments);
        }

        /** @var AssignmentInterface $assignment */
        foreach ($assignments as $assignment) {
            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);
            if ($quantity->isZero()) {
                return;
            }
        }

        // Remaining debit
        if (0 > $quantity) {
            throw new StockLogicException(sprintf(
                'Failed to dispatch sale item "%s" changed quantity debit over assigned stock units.',
                $item
            ));
        }

        // Remaining credit
        if (0 < $quantity) {
            $this->createAssignmentsForQuantity($item, $quantity);
        }
    }

    public function detachProductionItem(ProductionItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        /** @var AssignableInterface $item */

        // Remove stock assignments and schedule events
        foreach ($assignments as $assignment) {
            $this->assignmentUpdater->updateSold($assignment, new Decimal(0), false);
        }
    }

    public function assignProduction(ProductionInterface $production): void
    {
        foreach ($production->getProductionOrder()->getItems() as $item) {
            // Abort if not supported
            if (null === $assignments = $this->getAssignments($item)) {
                continue;
            }

            $quantity = $item->getQuantity()->mul($production->getQuantity());
            foreach ($assignments as $assignment) {
                $quantity -= $this->assignmentUpdater->updateShipped($assignment, $quantity, true);
            }

            if ($quantity->isZero()) {
                continue;
            }

            throw new StockLogicException(sprintf(
                'Failed to assign production item "%s".',
                $item->getDesignation()
            ));
        }
    }

    public function applyProduction(ProductionInterface $production): void
    {
        $cs = $this->persistenceHelper->getChangeSet($production, 'quantity');
        if (empty($cs)) {
            return;
        }

        $produced = $cs[1] - $cs[0];

        foreach ($production->getProductionOrder()->getItems() as $item) {
            // Abort if not supported
            if (null === $assignments = $this->getAssignments($item)) {
                continue;
            }

            $consumed = $item->getQuantity()->mul($produced);
            foreach ($assignments as $assignment) {
                $consumed -= $this->assignmentUpdater->updateShipped($assignment, $consumed, true);
            }

            if (!$consumed->isZero()) {
                throw new Exception('Failed to apply production item shipped quantity');
            }
        }
    }

    public function detachProduction(ProductionInterface $production): void
    {
        $produced = $production->getQuantity();
        $cs = $this->persistenceHelper->getChangeSet($production, 'quantity');
        if (!empty($cs)) {
            $produced = $cs[0];
        }

        foreach ($production->getProductionOrder()->getItems() as $item) {
            // Abort if not supported
            if (null === $assignments = $this->getAssignments($item)) {
                continue;
            }

            $consumed = $item->getQuantity()->mul($produced)->negate();
            foreach ($assignments as $assignment) {
                $consumed -= $this->assignmentUpdater->updateShipped($assignment, $consumed, true);
            }

            if (!$consumed->isZero()) {
                throw new Exception('Failed to detach production item shipped quantity');
            }
        }
    }

    public function assignShipmentItem(OrderShipmentItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // TODO sort assignments ?
        // TODO Use packaging format

        $quantity = $item->getQuantity();
        $return = $item->getShipment()->isReturn();

        if ($item->getShipment()->getState() === ShipmentStates::STATE_PREPARATION) {
            if ($return) {
                // Nothing to do
                return;
            } else {
                // Credit locked quantity
                $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                    return $this
                        ->assignmentUpdater
                        ->updateLocked($assignment, $quantity, true)
                        ->negate();
                };
            }
        } elseif ($return) {
            // Debit shipped quantity
            $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                return $this
                    ->assignmentUpdater
                    ->updateShipped($assignment, $quantity->negate(), true);
            };
        } else {
            // Debit shipped quantity
            $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                return $this
                    ->assignmentUpdater
                    ->updateShipped($assignment, $quantity, true)
                    ->negate();
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
        if ($quantity->isZero()) {
            return;
        }

        throw new StockLogicException(sprintf(
            'Failed to assign shipment item "%s".',
            $item->getSaleItem()->getDesignation()
        ));
    }

    public function applyShipmentItem(OrderShipmentItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)
        // TODO Use packaging format

        $shipment = $item->getShipment();

        if (!ShipmentStates::isStockableState($shipment, true)) {
            throw new LogicException('Shipment must be in a stockable state.');
        }

        $return = $shipment->isReturn();
        $quantityCs = $this->persistenceHelper->getChangeSet($item, 'quantity');

        $quantity = new Decimal(0);

        // If shipment state changed
        if (!empty($stateCs = $this->persistenceHelper->getChangeSet($shipment, 'state'))) {
            // Old quantity
            $quantity = $quantityCs[0] ?? $item->getQuantity();

            if (ShipmentStates::hasChangedFromPreparation($stateCs, true)) {
                if ($return) {
                    // Nothing to do
                    return; // TODO Really ?
                } else {
                    // Debit locked quantity
                    $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                        return $this
                            ->assignmentUpdater
                            ->updateLocked($assignment, $quantity->negate(), true);
                    };
                }
            } elseif (ShipmentStates::hasChangedToPreparation($stateCs, true)) {
                if ($return) {
                    // Credit shipped quantity
                    $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                        return $this
                            ->assignmentUpdater
                            ->updateShipped($assignment, $quantity, true)
                            ->negate();
                    };
                } else {
                    // Debit shipped quantity
                    $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                        return $this
                            ->assignmentUpdater
                            ->updateShipped($assignment, $quantity->negate(), true);
                    };
                }
            } else {
                throw new LogicException('Unexpected shipment state change.');
            }

            // Call on assignments
            foreach ($assignments as $assignment) {
                $quantity += $callable($assignment, $quantity);

                if ($quantity->isZero()) {
                    break;
                }
            }

            // New quantity
            $quantity = $quantityCs[1] ?? $item->getQuantity();
        } // If quantity change
        elseif (!empty($quantityCs)) {
            $quantity = ($quantityCs[1] ?? new Decimal(0))->sub($quantityCs[0] ?? new Decimal(0));
        }

        // Abort if zero quantity changed
        if ($quantity->isZero()) {
            return;
        }

        // Update assignments
        if (ShipmentStates::STATE_PREPARATION === $shipment->getState()) {
            if ($return) {
                // Nothing to do
                return;
            } else {
                // Credit locked quantity
                $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                    return $this
                        ->assignmentUpdater
                        ->updateLocked($assignment, $quantity, true)
                        ->negate();
                };
            }
        } elseif ($return) {
            // Debit shipped quantity
            $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                return $this
                    ->assignmentUpdater
                    ->updateShipped($assignment, $quantity->negate(), true);
            };
        } else {
            // Credit shipped quantity
            $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                return $this
                    ->assignmentUpdater
                    ->updateShipped($assignment, $quantity, true)
                    ->negate();
            };
        }

        // Call on assignments
        foreach ($assignments as $assignment) {
            $quantity += $callable($assignment, $quantity);

            if ($quantity->isZero()) {
                break;
            }
        }

        // Remaining quantity
        if ($quantity->isZero()) {
            return;
        }

        throw new StockLogicException(sprintf(
            'Failed to apply shipment item "%s".',
            $item->getSaleItem()->getDesignation()
        ));
    }

    public function detachShipmentItem(OrderShipmentItemInterface $item): void
    {
        // Abort if not supported
        if (null === $assignments = $this->getAssignments($item)) {
            return;
        }

        // TODO sort assignments ? (reverse for debit)
        // TODO Use packaging format

        // Get previous quantity if it has changed
        if ($this->persistenceHelper->isChanged($item, 'quantity')) {
            $quantity = $this->persistenceHelper->getChangeSet($item, 'quantity')[0] ?? new Decimal(0);
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
                $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                    return $this
                        ->assignmentUpdater
                        ->updateLocked($assignment, $quantity->negate(), true);
                };
            }
        } elseif ($return) {
            // Credit shipped quantity
            $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                return $this
                    ->assignmentUpdater
                    ->updateShipped($assignment, $quantity, true)
                    ->negate();
            };
        } else {
            // Debit shipped quantity
            $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                return $this
                    ->assignmentUpdater
                    ->updateShipped($assignment, $quantity->negate(), true);
            };
        }

        // Call on assignments
        foreach ($assignments as $assignment) {
            $quantity += $callable($assignment, $quantity);

            if ($quantity->isZero()) {
                break;
            }
        }

        // Remaining quantity
        if ($quantity->isZero()) {
            return;
        }

        throw new StockLogicException(sprintf(
            'Failed to detach shipment item "%s".',
            $item->getSaleItem()->getDesignation()
        ));
    }

    public function assignInvoiceLine(OrderInvoiceLineInterface $line): void
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
            $quantity += $this->assignmentUpdater->updateSold($assignment, $quantity->negate(), true);
        }

        // Remaining quantity
        if ($quantity->isZero()) {
            return;
        }

        throw new StockLogicException(sprintf(
            'Failed to assign invoice line "%s".',
            $line->getDesignation()
        ));
    }

    public function applyInvoiceLine(OrderInvoiceLineInterface $line): void
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
        if (!empty($ignoreStockCS) && ($ignoreStockCS[0] !== $ignoreStockCS[1])) {
            if ($ignoreStockCS[0]) {
                // Ignore stock disabled -> Debit sold quantity (use previous quantity)
                $quantity = $quantityCs[0] ?? $line->getQuantity();

                $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                    return $this->assignmentUpdater->updateSold($assignment, $quantity->negate(), true);
                };
            } elseif ($ignoreStockCS[1]) {
                // Ignore stock enabled -> Credit sold quantity
                $quantity = $line->getQuantity();

                $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                    return $this
                        ->assignmentUpdater
                        ->updateSold($assignment, $quantity, true)
                        ->negate();
                };
            }
        } elseif (!$invoice->isIgnoreStock() && !empty($quantityCs)) {
            // Ignore stock disabled -> Debit sold quantity (use previous quantity)
            $quantity = ($quantityCs[1] ?? new Decimal(0))->sub($quantityCs[0] ?? new Decimal(0));

            $callable = function (AssignmentInterface $assignment, Decimal $quantity): Decimal {
                return $this->assignmentUpdater->updateSold($assignment, $quantity->negate(), true);
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
                $this->createAssignmentsForQuantity($line->getOrderItem(), $quantity);

                return;
            }

            if ($quantity->isZero()) {
                break;
            }
        }

        // Remaining quantity
        if ($quantity->isZero()) {
            return;
        }

        throw new StockLogicException(sprintf(
            'Failed to apply invoice line "%s".',
            $line->getDesignation()
        ));
    }

    public function detachInvoiceLine(OrderInvoiceLineInterface $line): void
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
            $quantity = $this->persistenceHelper->getChangeSet($line, 'quantity')[0] ?? new Decimal(0);
        } else {
            $quantity = $line->getQuantity();
        }

        // Update assignments
        foreach ($assignments as $assignment) {
            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);
        }

        // Create assignments for remaining quantity
        if (0 < $quantity) {
            $this->createAssignmentsForQuantity($line->getOrderItem(), $quantity);

            return;
        }

        // Remaining quantity
        if ($quantity->isZero()) {
            return;
        }

        throw new StockLogicException(sprintf(
            'Failed to detach invoice line "%s".',
            $line->getDesignation()
        ));
    }

    /**
     * Returns the assignable stock assignments, or null if not supported.
     *
     * @return array<AssignmentInterface>|null
     */
    protected function getAssignments(AssignableInterface|ShipmentItemInterface|InvoiceLineInterface $item): ?array
    {
        if (!$item instanceof AssignableInterface && null === $item = $item->getSaleItem()) {
            return null;
        }

        if (!$this->supportsAssignment($item)) {
            return null;
        }

        return $item->getStockAssignments()->toArray();
    }

    /**
     * Creates the sale item assignments for the given quantity.
     *
     * @throws StockLogicException If assignment creation fails.
     */
    protected function createAssignmentsForQuantity(AssignableInterface $assignable, Decimal $quantity): void
    {
        if (0 >= $quantity) {
            return;
        }

        // Find enough available stock units
        $stockUnits = $this->unitResolver->findAssignable($assignable);

        $this->sortStockUnits($stockUnits);

        foreach ($stockUnits as $stockUnit) {
            // TODO Look for new assignment that could be used

            $assignment = $this->assignmentManager->create($assignable, $stockUnit);

            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);

            if ($quantity->isZero()) {
                return;
            }
        }

        // Remaining quantity
        if (0 < $quantity) {
            $stockUnit = $this->unitResolver->createBySubjectReference($assignable);

            $assignment = $this->assignmentManager->create($assignable, $stockUnit);

            $quantity -= $this->assignmentUpdater->updateSold($assignment, $quantity, true);
        }

        if ($quantity->isZero()) {
            return;
        }

        throw new StockLogicException(sprintf(
            'Failed to create assignments for item "%s".',
            $assignable->getDesignation()
        ));
    }

    /**
     * Sorts the stock assignments.
     *
     * @param array<AssignmentInterface> $assignments
     */
    protected function sortAssignments(array &$assignments): void
    {
        usort($assignments, function (AssignmentInterface $a1, AssignmentInterface $a2) {
            $u1 = $a1->getStockUnit();
            $u2 = $a2->getStockUnit();

            return $this->compareStockUnit($u1, $u2);
        });
    }

    /**
     * Sorts the stock units.
     *
     * @param array<StockUnitInterface> $units
     */
    protected function sortStockUnits(array &$units): void
    {
        usort($units, [$this, 'compareStockUnit']);
    }

    /**
     * Sorts the stock units for credit case (sold quantity).
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
     * Compares the units regarding their price.
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
        if ($u1->getNetPrice()->equals($u2->getNetPrice())) {
            return 0;
        }

        return $u1->getNetPrice()->compareTo($u2->getNetPrice());
    }

    /**
     * Compares the units regarding their estimated date of arrival.
     */
    protected function compareStockUnitByEda(StockUnitInterface $u1, StockUnitInterface $u2): int
    {
        $u1HasEda = null !== $u1->getEstimatedDateOfArrival();
        $u2HasEda = null !== $u2->getEstimatedDateOfArrival();

        if (!$u1HasEda && !$u2HasEda) {
            return 0;
        }

        if (!$u1HasEda && $u2HasEda) {
            return 1;
        }

        if ($u1HasEda && !$u2HasEda) {
            return -1;
        }

        if ($u1->getEstimatedDateOfArrival()->getTimestamp() === $u2->getEstimatedDateOfArrival()->getTimestamp()) {
            return 0;
        }

        return $u1->getEstimatedDateOfArrival() > $u2->getEstimatedDateOfArrival() ? 1 : -1;
    }
}
