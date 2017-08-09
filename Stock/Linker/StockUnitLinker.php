<?php

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitStateResolverInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderItemInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitLinker
 * @package Ekyna\Component\Commerce\Stock\Linker
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitLinker
{
    /**
     * @var PersistenceHelperInterface
     */
    private $persistenceHelper;

    /**
     * @var StockUnitResolverInterface
     */
    private $unitResolver;

    /**
     * @var StockUnitStateResolverInterface
     */
    private $stateResolver;

    /**
     * @var SaleFactoryInterface
     */
    private $saleFactory;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface      $persistenceHelper
     * @param StockUnitResolverInterface      $unitResolver
     * @param StockUnitStateResolverInterface $stateResolver
     * @param SaleFactoryInterface            $saleFactory
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitResolverInterface $unitResolver,
        StockUnitStateResolverInterface $stateResolver,
        SaleFactoryInterface $saleFactory
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->unitResolver = $unitResolver;
        $this->stateResolver = $stateResolver;
        $this->saleFactory = $saleFactory;
    }

    /**
     * Link the given supplier order item to new stock unit.
     *
     * @param SupplierOrderItemInterface $supplierOrderItem
     *
     * @throws LogicException
     */
    public function linkItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        // Find 'unlinked' stock units ordered (+ Cached 'new' stock units look up)
        if (null !== $stockUnit = $this->unitResolver->findLinkable($supplierOrderItem)) {
            $stockUnit->setSupplierOrderItem($supplierOrderItem);
        } else {
            $stockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);
        }

        $stockUnit
            ->setSupplierOrderItem($supplierOrderItem)
            ->setNetPrice($supplierOrderItem->getNetPrice())
            ->setOrderedQuantity($supplierOrderItem->getQuantity())
            ->setEstimatedDateOfArrival($supplierOrderItem->getOrder()->getEstimatedDateOfArrival());

        // Removes this stock unit from the resolver's cache
        $this->unitResolver->purge($stockUnit);

        // We want the sold quantity to be equal to the ordered quantity.
        // We don't care about shipped quantity as 'new' stock units can't be shipped.
        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getOrderedQuantity();
        if (0 >= $overflow) {
            $this->persistStockUnit($stockUnit);

            return;
        }

        // TODO What about pending stock units for the same sale item ?

        // New 'unlinked' stock unit for the sold quantity overflow
        $newStockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);

        $overflow -= $this->moveAssignments($stockUnit, $newStockUnit, $overflow);

        if (0 < $overflow) {
            throw new LogicException("Failed to dispatch assignments.");
        }
    }

    /**
     * Dispatches the ordered quantity change over assignments.
     *
     * @param SupplierOrderItemInterface $supplierOrderItem
     *
     * @throws LogicException
     */
    public function applyItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        if (!$this->persistenceHelper->isChanged($supplierOrderItem, 'quantity')) {
            return;
        }

        $cs = $this->persistenceHelper->getChangeSet($supplierOrderItem, 'quantity');
        if (0 == $cs[1] - $cs[0]) {
            return;
        }

        // Supplier order item has been previously linked to a stock unit.
        $stockUnit = $supplierOrderItem->getStockUnit();
        // Sync the ordered quantity
        $stockUnit->setOrderedQuantity($supplierOrderItem->getQuantity());

        // TODO update stock unit's net price if changed ?

        if ($stockUnit->getOrderedQuantity() < $stockUnit->getReceivedQuantity()) {
            throw new LogicException("Stock unit's ordered quantity can't be lower than received quantity.");
        }

        // We don't care about shipped quantities because of the 'ordered > received > shipped' rule.
        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getOrderedQuantity();
        // Assignments are sorted from the most recent to the most ancient
        $assignments = $this->sortAssignments($stockUnit->getStockAssignments()->toArray());
        // Abort if no overflow
        if (empty($assignments) || 0 == $overflow) {
            $this->persistStockUnit($stockUnit);

            return;
        }

        // Positive case : too much sold quantity
        if (0 < $overflow) {
            // Try to move sold overflow to other pending/ready stock units
            $targetStockUnits = $this->unitResolver->findPendingOrReady($supplierOrderItem);
            foreach ($targetStockUnits as $targetStockUnit) {
                // Skip the stock unit we're applying
                if ($targetStockUnit === $stockUnit) {
                    continue;
                }

                $overflow -= $this->moveAssignments($stockUnit, $targetStockUnit, $overflow);

                if (0 == $overflow) {
                    break; // We're done dispatching sold quantity
                }
            }

            // Move sold overflow to a new stock unit
            if (0 < $overflow) {
                // New 'unlinked' stock unit for the sold quantity overflow
                $newStockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);

                $overflow -= $this->moveAssignments($stockUnit, $newStockUnit, $overflow);
            }

            if (0 != $overflow) {
                throw new LogicException("Failed to apply supplier order item.");
            }

            return;
        }

        // Negative case : not enough sold quantity

        // TODO Try to move from assignments from new (not linked) stock units

        throw new LogicException("Not yet implemented.");
    }

    /**
     * Unlink the given supplier order item from its stock unit.
     *
     * @param SupplierOrderItemInterface $supplierOrderItem
     *
     * @throws LogicException
     */
    public function unlinkItem(SupplierOrderItemInterface $supplierOrderItem)
    {
        $stockUnit = $supplierOrderItem->getStockUnit();
        if (0 < $stockUnit->getReceivedQuantity() || 0 < $stockUnit->getShippedQuantity()) {
            throw new LogicException("Can't unlink supplier order item as it has been partially or fully received or shipped.");
        }

        // Unlink stock unit by setting supplier order item to null and ordered quantity to zero
        $stockUnit
            ->setSupplierOrderItem(null)
            ->setOrderedQuantity(0);

        if (0 === $soldQty = $stockUnit->getSoldQuantity()) {
            // Stock has no assignment
            // Remove the stock unit without scheduling event
            // (we don't want to trigger product's stock unit change listener)
            $this->persistenceHelper->remove($stockUnit, false);

            return;
        }

        // Assignments are sorted from the more recent to the most ancient
        $assignments = $this->sortAssignments($stockUnit->getStockAssignments()->toArray());

        // Try to move assignments to not closed stock units
        // TODO stock unit sort/priority
        $targetStockUnits = $this->unitResolver->findPendingOrReady($supplierOrderItem);
        foreach ($targetStockUnits as $targetStockUnit) {
            // Skip the stock unit we're unlinking
            if ($targetStockUnit === $stockUnit) {
                continue;
            }

            $available = $targetStockUnit->getSoldQuantity() - $targetStockUnit->getOrderedQuantity();
            if (0 >= $available) {
                continue;
            }

            foreach ($assignments as $assignment) {
                $saleItem = $assignment->getSaleItem();

                // Look for a target assignment with the same sale item
                $targetAssignment = null;
                $targetAssignments = $this->sortAssignments($targetStockUnit->getStockAssignments()->toArray());
                foreach ($targetAssignments as $ta) {
                    if ($ta->getSaleItem() === $saleItem) {
                        $targetAssignment = $ta;
                        break;
                    }
                }

                // If there is not enough available sold quantity
                // to fully move the assignment to the target stock unit,
                // split the assignment
                if ($available < $assignment->getSoldQuantity()) {
                    // New assignment for the available sold quantity
                    if (null !== $targetAssignment) {
                        $targetAssignment->setSoldQuantity($targetAssignment->getSoldQuantity() + $available);
                    } else {
                        $targetAssignment = $this->saleFactory->createStockAssignmentForItem($saleItem);
                        $targetAssignment
                            ->setSaleItem($saleItem)
                            ->setStockUnit($targetStockUnit)
                            ->setSoldQuantity($available);
                    }

                    // Persist without scheduling event
                    $this->persistenceHelper->persistAndRecompute($targetAssignment, false);
                    $this->persistenceHelper->persistAndRecompute($assignment, false);

                    // Update the assignment's sold quantity
                    $assignment->setSoldQuantity($assignment->getSoldQuantity() - $available);

                    $soldQty -= $available;
                    $available = 0;
                } else {
                    if ($targetAssignment) {
                        // Add sold quantity to the target assignment
                        $targetAssignment->setSoldQuantity($targetAssignment->getSoldQuantity() + $assignment->getSoldQuantity());

                        // Persist without scheduling event
                        $this->persistenceHelper->persistAndRecompute($targetAssignment, false);

                        // Removes the stock unit without scheduling event
                        $assignment->setStockUnit(null);
                        $this->persistenceHelper->remove($assignment, false);
                    } else {
                        // There is enough available sold quantity to move the assignment to the target stock unit
                        $assignment->setStockUnit($targetStockUnit);

                        // Persist without scheduling event
                        $this->persistenceHelper->persistAndRecompute($assignment, false);
                    }

                    $available -= $assignment->getSoldQuantity();
                    $soldQty -= $assignment->getSoldQuantity();
                }

                // TODO resolve the target stock unit's state

                // Persist without scheduling event (we don't want to trigger product's stock unit change listener)
                $this->persistenceHelper->persistAndRecompute($targetStockUnit, false);

                if (0 == $soldQty) {
                    break 2; // We're done with re-assignment
                } elseif (0 == $available) {
                    continue 2; // Next stock unit
                }
            }
        }

        if (0 < $soldQty) {
            // Try to merge assignments with a linkable stock unit's assignments
            $targetStockUnit = $this->unitResolver->findLinkable($supplierOrderItem);
            if (null !== $targetStockUnit && $stockUnit !== $targetStockUnit) {
                $assignments = $this->sortAssignments($stockUnit->getStockAssignments()->toArray());
                foreach ($assignments as $assignment) {
                    $saleItem = $assignment->getSaleItem();

                    $targetAssignments = $this->sortAssignments($targetStockUnit->getStockAssignments()->toArray());
                    foreach ($targetAssignments as $targetAssignment) {
                        if ($targetAssignment->getSaleItem() === $saleItem) {
                            $targetAssignment->setSoldQuantity(
                                $targetAssignment->getSoldQuantity() + $assignment->getSoldQuantity()
                            );

                            // Persist without scheduling event
                            $this->persistenceHelper->persistAndRecompute($targetAssignment, false);

                            // Removes the stock unit without scheduling event
                            $assignment->setStockUnit(null);
                            $this->persistenceHelper->remove($assignment, false);

                            $soldQty -= $assignment->getSoldQuantity();

                            continue 2;
                        }
                    }

                    // Move the assignment to the target stock unit
                    $assignment->setStockUnit($targetStockUnit);

                    // Persist without scheduling event
                    $this->persistenceHelper->persistAndRecompute($assignment, false);

                    $soldQty -= $assignment->getSoldQuantity();
                }
            }
        }

        if (empty($stockUnit->getStockAssignments())) {
            if (0 < $soldQty) {
                throw new LogicException("Failed to unlink supplier order item.");
            }

            $this->persistenceHelper->remove($stockUnit, false);
        }

        if (0 == $soldQty) {
            throw new LogicException("Failed to unlink supplier order item.");
        }

        $stockUnit->setSoldQuantity($soldQty);

        // TODO resolve the target stock unit's state

        // Persist the stock unit without scheduling event
        // (we don't want to trigger product's stock unit change listener)
        $this->persistenceHelper->persistAndRecompute($stockUnit, false);
    }

    /**
     * Moves (or splits) assignments from the source stock unit
     * to the target stock unit for the given quantity.
     *
     * @param StockUnitInterface $sourceUnit
     * @param StockUnitInterface $targetUnit
     * @param float              $quantity
     *
     * @return float The quantity indeed moved
     */
    private function moveAssignments(StockUnitInterface $sourceUnit, StockUnitInterface $targetUnit, $quantity)
    {
        if (0 >= $quantity) {
            throw new InvalidArgumentException("Quantity must be greater than zero.");
        }

        $moved = 0;

        // If the target stock unit is linked to a supplier order item,
        // don't create overflow on it.
        if (null !== $targetUnit->getSupplierOrderItem()) {
            $available = $targetUnit->getSoldQuantity() - $targetUnit->getOrderedQuantity();
            if (0 >= $available) {
                // Abort because sold quantity would become greater than ordered
                return $moved;
            }

            // Don't move more than available
            if ($quantity > $available) {
                $quantity = $available;
            }
        }


        $sourceAssignments = $this->sortAssignments($sourceUnit->getStockAssignments()->toArray());
        $targetAssignments = $targetUnit->getStockAssignments();

        foreach ($sourceAssignments as $sourceAssignment) {
            // Move assignment
            if ($quantity >= $sourceAssignment->getSoldQuantity()) {
                $delta = $sourceAssignment->getSoldQuantity();
                $sourceAssignment->setStockUnit($targetUnit);
                $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $delta);
                $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $delta);

                $this->persistAssignment($sourceAssignment);

                $moved += $delta;
                $quantity -= $delta;
                if (0 == $quantity) {
                    break;
                }

                continue;
            }

            // Split assignment
            $saleItem = $sourceAssignment->getSaleItem();

            // Look for a target assignment with the same sale item
            $targetAssignment = null;
            foreach ($targetAssignments as $ta) {
                if ($ta->getSaleItem() === $saleItem) {
                    $targetAssignment = $ta;
                    break;
                }
            }
            // If not found, create a new assignment
            if (null === $targetAssignment) {
                $targetAssignment = $this->saleFactory->createStockAssignmentForItem($saleItem);
                $targetAssignment
                    ->setSaleItem($saleItem)
                    ->setStockUnit($targetUnit);
            }

            // Add quantity to target assignment and unit
            $targetAssignment->setSoldQuantity($targetAssignment->getSoldQuantity() + $quantity);
            $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $quantity);

            $this->persistAssignment($targetAssignment);

            // Remove quantity from source unit and assignment
            $sourceAssignment->setSoldQuantity($sourceAssignment->getSoldQuantity() - $quantity);
            $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $quantity);

            $this->persistAssignment($sourceAssignment);

            $moved += $quantity;

            break;
        }

        $this->persistStockUnit($targetUnit);
        $this->persistStockUnit($sourceUnit);

        return $moved;
    }

    /**
     * Persists (or removes) the stock unit.
     *
     * @param StockUnitInterface $stockUnit
     */
    private function persistStockUnit(StockUnitInterface $stockUnit)
    {
        // If empty, remove without scheduling event
        if ($stockUnit->isEmpty()) {
            // TODO Test if assignments is empty too ?
            $stockUnit->setSupplierOrderItem(null);
            $this->persistenceHelper->remove($stockUnit, false);

            return;
        }

        // Resolve the target stock unit's state
        $this->stateResolver->resolve($stockUnit);

        // Persist without scheduling event
        $this->persistenceHelper->persistAndRecompute($stockUnit, false);
    }

    /**
     * Persists (or removes) the stock assignment.
     *
     * @param StockAssignmentInterface $assignment
     */
    private function persistAssignment(StockAssignmentInterface $assignment)
    {
        // Remove if empty
        if (0 == $assignment->getSoldQuantity()) {
            $assignment->setStockUnit(null);
            $this->persistenceHelper->remove($assignment, false);

            return;
        }

        // Persist without scheduling event
        $this->persistenceHelper->persistAndRecompute($assignment, false);
    }

    /**
     * Sort assignments from the most recent to the most ancient.
     *
     * @param StockAssignmentInterface[] $assignments
     *
     * @return StockAssignmentInterface[]
     */
    private function sortAssignments(array $assignments)
    {
        usort($assignments, function (StockAssignmentInterface $a, StockAssignmentInterface $b) {
            $aDate = $a->getSaleItem()->getSale()->getCreatedAt();
            $bDate = $b->getSaleItem()->getSale()->getCreatedAt();

            if ($aDate == $bDate) {
                return 0;
            }

            return $aDate < $bDate ? 1 : -1;
        });

        return $assignments;
    }

    /**
     * Returns the most ancient assignment.
     *
     * @param StockAssignmentInterface[] $assignments
     *
     * @return StockAssignmentInterface|null
     */
    private function getLastAssignment(array $assignments)
    {
        if (empty($assignments)) {
            return null;
        }

        $assignments = $this->sortAssignments($assignments);

        return reset($assignments);
    }
}
