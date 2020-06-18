<?php

namespace Ekyna\Component\Commerce\Stock\Dispatcher;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Logger\StockLoggerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockAssignmentManagerInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface as Assignment;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface as Unit;

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
        Unit $sourceUnit,
        Unit $targetUnit,
        float $quantity,
        int $direction = SORT_DESC
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

        $sourceAssignments = $this->sortAssignments($sourceUnit->getStockAssignments()->toArray(), $direction);

        foreach ($sourceAssignments as $assignment) {
            if (0 == $delta = $this->moveAssignment($assignment, $targetUnit, $quantity)) {
                continue;
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
     * Move the given assignment to the given unit for the given sold quantity.
     *
     * @param Assignment $assignment
     * @param Unit       $targetUnit
     * @param float                          $quantity
     *
     * @return float The quantity moved
     */
    public function moveAssignment(Assignment $assignment, Unit $targetUnit, float $quantity): float
    {
        // Don't move shipped/locked quantity
        $quantity = min($quantity, $assignment->getReleasableQuantity());
        if (0 >= $quantity) { // TODO Packaging format
            return 0;
        }

        $sourceUnit = $assignment->getStockUnit();
        $saleItem = $assignment->getSaleItem();

        // Debit source unit's sold quantity
        $this->logger->unitSold($sourceUnit, -$quantity);
        $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $quantity);

        // Credit target unit's sold quantity
        $this->logger->unitSold($targetUnit, $quantity);
        $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $quantity);

        // Look for a target assignment with the same sale item
        $merge = null;
        foreach ($targetUnit->getStockAssignments() as $m) {
            if ($m->getSaleItem() === $saleItem) {
                $merge = $m;
                break;
            }
        }

        if ($quantity == $assignment->getSoldQuantity()) {
            if (null !== $merge) {
                // Credit quantity to mergeable assignment
                $this->logger->assignmentSold($merge, $quantity);
                $merge->setSoldQuantity($merge->getSoldQuantity() + $quantity);
                $this->assignmentManager->persist($merge);

                // Debit quantity from source assignment
                $this->logger->assignmentSold($assignment, 0, false); // TODO log removal ?
                $assignment->setSoldQuantity(0);
                $this->assignmentManager->remove($assignment, true);
            } else {
                // Move source assignment to target unit
                $this->logger->assignmentUnit($assignment, $targetUnit);
                $assignment->setStockUnit($targetUnit);
                $this->assignmentManager->persist($assignment);
            }
        } else {
            // Debit quantity from source assignment
            $this->logger->assignmentSold($assignment, -$quantity);
            $assignment->setSoldQuantity($assignment->getSoldQuantity() - $quantity);
            $this->assignmentManager->persist($assignment);

            if (null !== $merge) {
                // Credit quantity to mergeable assignment
                $this->logger->assignmentSold($merge, $quantity);
                $merge->setSoldQuantity($merge->getSoldQuantity() + $quantity);
                $this->assignmentManager->persist($merge);
            } else {
                // Credit quantity to new assignment
                $create = $this->assignmentManager->create($saleItem, $targetUnit);
                $this->logger->assignmentSold($create, $quantity, false);
                $create->setSoldQuantity($quantity);
                $this->assignmentManager->persist($create);
            }
        }

        return $quantity;
    }

    /**
     * Sort assignments from the most recent to the most ancient.
     *
     * @param Assignment[] $assignments
     * @param int          $direction
     *
     * @return Assignment[]
     */
    private function sortAssignments(array $assignments, $direction = SORT_DESC): array
    {
        usort($assignments, function (Assignment $a, Assignment $b) use ($direction) {
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
