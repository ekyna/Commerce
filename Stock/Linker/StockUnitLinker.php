<?php

namespace Ekyna\Component\Commerce\Stock\Linker;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Exception\LogicException;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
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
    protected $persistenceHelper;

    /**
     * @var StockUnitResolverInterface
     */
    protected $unitResolver;

    /**
     * @var StockUnitStateResolverInterface
     */
    private $stateResolver;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;


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

        // TODO resolve the stock unit's state

        // Persist without scheduling event (we don't want to trigger product's stock unit change listener)
        $this->persistenceHelper->persistAndRecompute($stockUnit, false);
        // Removes this stock unit from the resolver's cache
        $this->unitResolver->purge($stockUnit);

        // We want the sold quantity to be equal to the ordered quantity.
        // We don't care about shipped quantity as 'new' stock units can't be shipped.
        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getOrderedQuantity();
        if (0 >= $overflow) {
            return;
        }

        // New 'unlinked' stock unit for the sold quantity overflow
        $newStockUnit = $this->unitResolver->createBySubjectRelative($supplierOrderItem);
        $newStockUnit->setSoldQuantity($overflow);

        // Assignments are sorted from the more recent to the most ancient
        $assignments = $this->sortAssignments($stockUnit->getStockAssignments()->toArray());

        foreach ($assignments as $assignment) {
            // Case where the assignment has more sold quantity than the overflow
            // -> Split the overflow into a new assignment for the new stock unit
            if ($overflow < $assignment->getSoldQuantity()) { // TODO packaging format + bccomp ?
                $remaining = $assignment->getSoldQuantity() - $overflow;
                $assignment->setSoldQuantity($remaining);
                $overflow = 0;

                // New assignment for the sold quantity overflow
                $saleItem = $assignment->getSaleItem();
                $newAssignment = $this->saleFactory->createStockAssignmentForItem($saleItem);
                $newAssignment
                    ->setSaleItem($saleItem)
                    ->setSoldQuantity($overflow)
                    ->setStockUnit($newStockUnit);

                // Persist without scheduling event
                $this->persistenceHelper->persistAndRecompute($newAssignment, false);
            } else {
                // Case where the assignment has less sold quantity than the overflow
                // -> Move the assignment to the new stock unit
                $assignment->setStockUnit($newStockUnit);
                $overflow -= $assignment->getSoldQuantity();
            }

            // Persist without scheduling event
            $this->persistenceHelper->persistAndRecompute($assignment, false);

            if (0 == $overflow) {
                break; // We're done
            }
        }

        if (0 < $overflow) {
            throw new LogicException("Failed to dispatch assignments.");
        }

        $stockUnit->setSoldQuantity($stockUnit->getOrderedQuantity());

        // TODO resolve the new stock unit's state

        // Persist without scheduling event (we don't want to trigger product's stock unit change listener)
        $this->persistenceHelper->persistAndRecompute($stockUnit, false);
        $this->persistenceHelper->persistAndRecompute($newStockUnit, false);
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
        $deltaQuantity = $cs[1] - $cs[0];

        if (0 == $deltaQuantity) {
            return;
        }

        $stockUnit = $supplierOrderItem->getStockUnit();
        $stockUnit->setOrderedQuantity($stockUnit->getOrderedQuantity() + $deltaQuantity);

        if ($stockUnit->getOrderedQuantity() < $stockUnit->getReceivedQuantity()) {
            throw new LogicException("Stock unit's ordered quantity can't be lower than received quantity.");
        }
        // We don't care about shipped quantities because of the 'ordered > received > shipped' rule.

        $overflow = $stockUnit->getSoldQuantity() - $stockUnit->getOrderedQuantity();

        // Assignments are sorted from the more recent to the most ancient
        $assignments = $this->sortAssignments($stockUnit->getStockAssignments()->toArray());

        if (0 < $overflow) {
            // Positive case : too much sold quantity

            foreach ($assignments as $assignment) {
                $saleItem = $assignment->getSaleItem();

                // Try to move sold overflow to other pending/ready stock units
                $targetStockUnits = $this->unitResolver->findPendingOrReady($supplierOrderItem);
                foreach ($targetStockUnits as $targetStockUnit) {
                    // Skip the stock unit we're applying
                    if ($targetStockUnit === $stockUnit) {
                        continue;
                    }

                    $targetQuantity = $targetStockUnit->getOrderedQuantity() - $targetStockUnit->getSoldQuantity();
                    if (0 >= $targetQuantity) {
                        // Skip balanced target stock unit, as we would just move the problem
                        continue;
                    }
                    if ($targetQuantity > $overflow) {
                        $targetQuantity = $overflow;
                    }

                    // Look for a target assignment with the same sale item
                    $targetAssignment = null;
                    $targetAssignments = $this->sortAssignments($targetStockUnit->getStockAssignments()->toArray());
                    foreach ($targetAssignments as $ta) {
                        if ($ta->getSaleItem() === $saleItem) {
                            $targetAssignment = $ta->setSoldQuantity($ta->getSoldQuantity() + $targetQuantity);
                            break;
                        }
                    }
                    // If not found, create a new assignment
                    if (null === $targetAssignment) {
                        $targetAssignment = $this->saleFactory->createStockAssignmentForItem($saleItem);
                        $targetAssignment
                            ->setSaleItem($saleItem)
                            ->setStockUnit($targetStockUnit)
                            ->setSoldQuantity($targetQuantity);
                    }

                    $targetStockUnit->setSoldQuantity($targetStockUnit->getSoldQuantity() + $targetQuantity);
                    // Persist without scheduling event

                    // TODO resolve the target stock unit's state
                    $this->persistenceHelper->persistAndRecompute($targetStockUnit, false);
                    $this->persistenceHelper->persistAndRecompute($targetAssignment, false);

                    $assignment->setSoldQuantity($assignment->getSoldQuantity() - $targetQuantity);
                    $stockUnit->setSoldQuantity($stockUnit->getSoldQuantity() - $targetQuantity);

                    // TODO resolve the stock unit's state
                    $this->persistenceHelper->persistAndRecompute($stockUnit, false);
                    if (0 == $assignment->getSoldQuantity()) {
                        $assignment->setStockUnit(null);
                        $this->persistenceHelper->remove($assignment, false);
                    } else {
                        $this->persistenceHelper->persistAndRecompute($assignment, false);
                    }


                    $overflow -= $targetQuantity;
                    $deltaQuantity -= $targetQuantity;

                    if (0 == $overflow) {
                        break 2; // We're done dispatching sold quantity
                    }
                }

                // TODO same with new stock unit
            }
        } elseif (0 > $overflow) {
            // Negative case : not enough sold quantity

            // Debit case
            foreach ($assignments as $assignment) {

            }
        }

        // TODO May happen / Not necessarily a bug
        if (0 != $deltaQuantity || 0 != $overflow) {
            throw new LogicException("Failed to apply supplier order item.");
        }
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
     * Sort assignments regarding to sales 'created at' dates.
     *
     * @param array $assignments
     *
     * @return StockAssignmentInterface[]
     */
    protected function sortAssignments(array $assignments)
    {
        usort($assignments, function (StockAssignmentInterface $a, StockAssignmentInterface $b) {
            $aDate = $a->getSaleItem()->getSale()->getCreatedAt();
            $bDate = $b->getSaleItem()->getSale()->getCreatedAt();

            if ($aDate == $bDate) {
                return 0;
            }

            return $aDate > $bDate ? 1 : -1;
        });

        return $assignments;
    }
}
