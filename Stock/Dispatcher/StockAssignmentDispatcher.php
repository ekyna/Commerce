<?php

namespace Ekyna\Component\Commerce\Stock\Dispatcher;

use Ekyna\Component\Commerce\Common\Factory\SaleFactoryInterface;
use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAssignmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockAssignmentDispatcher
 * @package Ekyna\Component\Commerce\Stock\Dispatcher
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAssignmentDispatcher implements StockAssignmentDispatcherInterface
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var SaleFactoryInterface
     */
    protected $saleFactory;

    /**
     * @var StockUnitManagerInterface
     */
    protected $unitManager;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param SaleFactoryInterface       $saleFactory
     * @param StockUnitManagerInterface  $unitManager
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        SaleFactoryInterface $saleFactory,
        StockUnitManagerInterface $unitManager
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->saleFactory = $saleFactory;
        $this->unitManager = $unitManager;
    }

    /**
     * @inheritdoc
     */
    public function moveAssignments(StockUnitInterface $sourceUnit, StockUnitInterface $targetUnit, $quantity)
    {
        if (0 >= $quantity) {
            throw new StockLogicException("Quantity must be greater than zero.");
        }

        $moved = 0;

        // If the target stock unit is linked to a supplier order item,
        // don't create overflow on it.
        if (0 < $max = $targetUnit->getOrderedQuantity() + $targetUnit->getAdjustedQuantity()) {
            $available = $max - $targetUnit->getSoldQuantity();
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

            // If no target assignment to merge into, move assignment
            if (null === $targetAssignment && $quantity >= $sourceAssignment->getSoldQuantity()) {
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

            // If not found, create a new assignment
            if (null === $targetAssignment) {
                $targetAssignment = $this->saleFactory->createStockAssignmentForItem($saleItem);
                $targetAssignment
                    ->setSaleItem($saleItem)
                    ->setStockUnit($targetUnit);
            }

            // Limit to assignment's non shipped quantity
            $delta = min($sourceAssignment->getSoldQuantity() - $sourceAssignment->getShippedQuantity(), $quantity);

            // Add quantity to target assignment and unit
            $targetAssignment->setSoldQuantity($targetAssignment->getSoldQuantity() + $delta);
            $targetUnit->setSoldQuantity($targetUnit->getSoldQuantity() + $delta);

            $this->persistAssignment($targetAssignment);

            // Remove quantity from source unit and assignment
            $sourceAssignment->setSoldQuantity($sourceAssignment->getSoldQuantity() - $delta);
            $sourceUnit->setSoldQuantity($sourceUnit->getSoldQuantity() - $delta);

            $this->persistAssignment($sourceAssignment);

            $moved += $delta;
            $quantity -= $delta;
            if (0 == $quantity) {
                break;
            }

            break;
        }

        $this->unitManager->persistOrRemove($targetUnit);
        $this->unitManager->persistOrRemove($sourceUnit);

        return $moved;
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
            $this->persistenceHelper->remove($assignment, true);

            return;
        }

        // Persist without scheduling event
        $this->persistenceHelper->persistAndRecompute($assignment, true);
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
}