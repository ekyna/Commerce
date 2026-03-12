<?php

declare(strict_types=1);

namespace Ekyna\Component\Commerce\Stock\Overflow;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcherInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

use function is_null;

/**
 * Class OverflowHandler
 * @package Ekyna\Component\Commerce\Stock\Overflow
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OverflowHandler implements OverflowHandlerInterface
{
    public function __construct(
        protected readonly PersistenceHelperInterface $persistenceHelper,
        protected readonly StockUnitResolverInterface $unitResolver,
        protected readonly StockAssignmentDispatcherInterface $assignmentDispatcher
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(StockUnitInterface $stockUnit): bool
    {
        // TODO Abort if stock unit is new ...

        // We don't care about shipped quantities because of the 'ordered > received > shipped' rule.
        $overflow = $stockUnit->getSoldQuantity()
            - $stockUnit->getOrderedQuantity()
            - $stockUnit->getAdjustedQuantity();

        // Abort if no overflow
        if ($overflow->isZero()) {
            return false;
        }

        $subject = $stockUnit->getSubject();

        // Positive case : too much sold quantity
        if ($overflow->isPositive()) {
            // Try to move sold overflow to other pending/ready stock units
            // TODO prefer ready units with enough quantity
            $targetStockUnits = $this->unitResolver->findPendingOrReady($subject);
            foreach ($targetStockUnits as $targetStockUnit) {
                // Skip the stock unit we're handling
                if ($targetStockUnit === $stockUnit) {
                    continue;
                }

                $overflow -= $this->assignmentDispatcher->moveAssignments($stockUnit, $targetStockUnit, $overflow);

                if ($overflow->isZero()) {
                    return true; // We're done dispatching sold quantity
                }
            }

            // Try to move sold overflow to a assignable stock units
            $targetStockUnits = $this->unitResolver->findAssignable($subject);
            foreach ($targetStockUnits as $targetStockUnit) {
                // Skip the stock unit we're handling
                if ($targetStockUnit === $stockUnit) {
                    continue;
                }

                $overflow -= $this->assignmentDispatcher->moveAssignments($stockUnit, $targetStockUnit, $overflow);

                if ($overflow->isZero()) {
                    return true; // We're done dispatching sold quantity
                }
            }

            // Move sold overflow to a new stock unit
            if ($overflow->isPositive()) {
                // We did not found another pending or assignable stock unit to move assignments to.
                // If current stock unit has its state to 'new', just stop here.
                if (StockUnitStates::STATE_NEW === $stockUnit->getState()) {
                    return false;
                }

                $newStockUnit = $this->unitResolver->createBySubject($subject, $stockUnit);

                // Pre persist stock unit
                $this->persistenceHelper->persistAndRecompute($newStockUnit, false);

                $overflow -= $this->assignmentDispatcher->moveAssignments($stockUnit, $newStockUnit, $overflow);
            }

            if (!$overflow->isZero()) {
                throw new StockLogicException('Failed to fix stock unit sold quantity overflow.');
            }

            return true;
        }

        // Don't move assignment to a non-linked stock unit
        if (is_null($stockUnit->getSupplierOrderItem()) && is_null($stockUnit->getProductionOrder())) {
            return false;
        }

        // Negative case : not enough sold quantity
        if (null !== $sourceUnit = $this->unitResolver->findLinkable($subject)) {
            if ($sourceUnit === $stockUnit) {
                return false;
            }
            if (0 != $this->assignmentDispatcher->moveAssignments($sourceUnit, $stockUnit, $overflow->negate(), SORT_ASC)) {
                return true;
            }
        }

        return false;
    }
}
