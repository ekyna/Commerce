<?php

namespace Ekyna\Component\Commerce\Stock\Dispatcher;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;
use Ekyna\Component\Commerce\Stock\Logger\StockLoggerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;

/**
 * Class StockAssignmentDispatcher
 * @package Ekyna\Component\Commerce\Stock\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentDispatcher implements StockAssignmentDispatcherInterface
{
    /**
     * @var StockAssignmentManagerInterface
     */
    protected $assignmentManager;

    /**
     * @var StockUnitManagerInterface
     */
    protected $unitManager;

    /**
     * @var StockLoggerInterface
     */
    protected $logger;


    /**
     * Constructor.
     *
     * @param StockAssignmentManagerInterface $assignmentManager
     * @param StockUnitManagerInterface       $unitManager
     * @param StockLoggerInterface            $logger
     */
    public function __construct(
        StockAssignmentManagerInterface $assignmentManager,
        StockUnitManagerInterface $unitManager,
        StockLoggerInterface $logger
    ) {
        $this->assignmentManager = $assignmentManager;
        $this->unitManager = $unitManager;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function moveAssignments(
        StockUnitInterface $sourceUnit,
        StockUnitInterface $targetUnit,
        $quantity,
        $direction = SORT_DESC
    ): float {
        if (0 >= $quantity) {
            throw new StockLogicException("Quantity must be greater than zero.");
        }

        if ($sourceUnit === $targetUnit) {
            throw new StockLogicException("Source and target units are the same.");
        }

        // Don't move more than reservable (don't create overflow on target unit)
        $quantity = min($quantity, $targetUnit->getReservableQuantity());
        if (0 >= $quantity) {
            // Abort because sold quantity would become greater than ordered
            return 0;
        }

        $moved = 0;

        /**
         * TODO Use combination to move the less assignments:
         * @see \Ekyna\Component\Commerce\Stock\Prioritizer\UnitCandidate::getCombination()
         */
        $sourceAssignments = $this->sortAssignments($sourceUnit->getStockAssignments()->toArray(), $direction);
        /** @var StockAssignmentInterface[] $targetAssignments */
        $targetAssignments = $targetUnit->getStockAssignments()->toArray();

        foreach ($sourceAssignments as $assignment) {
            /**
             * TODO Refactor with:
             * @see \Ekyna\Component\Commerce\Stock\Prioritizer\StockPrioritizer::moveAssignment()
             */

            $saleItem = $assignment->getSaleItem();

            // Don't move assignments of preparation sales.
            $sale = $saleItem->getSale();
            if (
                $sale instanceof ShipmentSubjectInterface &&
                $sale->getShipmentState() === ShipmentStates::STATE_PREPARATION
            ) {
                continue;
            }

            // Don't move shipped quantity
            $delta = min($quantity, $assignment->getSoldQuantity() - $assignment->getShippedQuantity());
            if (0 >= $delta) {
                continue;
            }

            // Add quantity to target unit
            $this->logger->unitSold($targetUnit, $delta);
            $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $delta);

            // Remove quantity from source unit
            $this->logger->unitSold($sourceUnit, -$delta);
            $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $delta);

            // Look for a target assignment with the same sale item
            $merge = null;
            foreach ($targetAssignments as $m) {
                if ($m->getSaleItem() === $saleItem) {
                    $merge = $m;
                    break;
                }
            }

            if ($delta == $assignment->getSoldQuantity()) {
                if (null !== $merge) {
                    // Credit quantity to mergeable assignment
                    $this->logger->assignmentSold($merge, $delta);
                    $merge->setSoldQuantity($merge->getSoldQuantity() + $delta);
                    $this->assignmentManager->persist($merge);

                    // Debit quantity from source assignment
                    $this->logger->assignmentSold($assignment, 0, false); // TODO log removal ?
                    $assignment->setSoldQuantity(0);
                    $this->assignmentManager->remove($assignment);
                } else {
                    // Move source assignment to target unit
                    $this->logger->assignmentUnit($assignment, $targetUnit);
                    $assignment->setStockUnit($targetUnit);
                    $this->assignmentManager->persist($assignment);
                }
            } else {
                // Debit quantity from source assignment
                $this->logger->assignmentSold($assignment, -$delta);
                $assignment->setSoldQuantity($assignment->getSoldQuantity() - $delta);
                $this->assignmentManager->persist($assignment);

                if (null !== $merge) {
                    // Credit quantity to mergeable assignment
                    $this->logger->assignmentSold($merge, $delta);
                    $merge->setSoldQuantity($merge->getSoldQuantity() + $delta);
                    $this->assignmentManager->persist($merge);
                } else {
                    // Credit quantity to new assignment
                    $create = $this->assignmentManager->create($saleItem, $targetUnit);
                    $this->logger->assignmentSold($create, $delta, false);
                    $create->setSoldQuantity($delta);
                    $this->assignmentManager->persist($create);
                }
            }

            $moved += $delta;
            $quantity -= $delta;
            if (0 == $quantity) {
                break;
            }
        }

        $this->unitManager->persistOrRemove($targetUnit);
        $this->unitManager->persistOrRemove($sourceUnit);

        return $moved;
    }

    /**
     * Sort assignments from the most recent to the most ancient.
     *
     * @param StockAssignmentInterface[] $assignments
     * @param int                        $direction
     *
     * @return StockAssignmentInterface[]
     */
    private function sortAssignments(array $assignments, $direction = SORT_DESC): array
    {
        usort($assignments, function (StockAssignmentInterface $a, StockAssignmentInterface $b) use ($direction) {
            $aDate = $a->getSaleItem()->getSale()->getCreatedAt();
            $bDate = $b->getSaleItem()->getSale()->getCreatedAt();

            if ($aDate == $bDate) {
                return 0;
            }

            if ($direction === SORT_ASC) {
                return $aDate < $bDate ? -1 : 1;
            }

            return $aDate > $bDate ? -1 : 1;
        });

        return $assignments;
    }
}
