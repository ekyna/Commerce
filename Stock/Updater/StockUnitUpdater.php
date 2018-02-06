<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Dispatcher\StockAssignmentDispatcherInterface;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Resolver\StockUnitResolverInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockUnitUpdater
 * @package Ekyna\Component\Commerce\Stock\Updater
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitUpdater implements StockUnitUpdaterInterface
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
     * @var StockUnitManagerInterface
     */
    protected $unitManager;

    /**
     * @var StockAssignmentDispatcherInterface
     */
    protected $assignmentDispatcher;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface         $persistenceHelper
     * @param StockUnitResolverInterface         $unitResolver
     * @param StockUnitManagerInterface          $unitManager
     * @param StockAssignmentDispatcherInterface $assignmentDispatcher
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitResolverInterface $unitResolver,
        StockUnitManagerInterface $unitManager,
        StockAssignmentDispatcherInterface $assignmentDispatcher
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->unitResolver = $unitResolver;
        $this->unitManager = $unitManager;
        $this->assignmentDispatcher = $assignmentDispatcher;
    }

    /**
     * @inheritdoc
     */
    public function updateOrdered(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getOrderedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected ordered quantity.");
        }

        // Prevent ordered quantity to be set as lower than the received quantity
        if ($quantity < $stockUnit->getReceivedQuantity()) {
            throw new StockLogicException("The ordered quantity can't be lower than the received quantity.");
        }

        $stockUnit->setOrderedQuantity($quantity);

        if ($this->handleOverflow($stockUnit)) {
            // Stock unit persistence has been made by assignment dispatcher.
            return;
        }

        $this->unitManager->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateReceived(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getReceivedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected received quantity.");
        }

        // Prevent received quantity to be set as greater than the ordered quantity
        if ($quantity > $stockUnit->getOrderedQuantity()) {
            throw new StockLogicException("The received quantity can't be greater than the ordered quantity.");
        }

        $stockUnit->setReceivedQuantity($quantity);

        $this->unitManager->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateAdjusted(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getAdjustedQuantity() + $quantity;
        }

        $stockUnit->setAdjustedQuantity($quantity);

        if ($this->handleOverflow($stockUnit)) {
            // Stock unit persistence has been made by assignment dispatcher.
            return;
        }

        $this->unitManager->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateSold(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getSoldQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected sold quantity.");
        }

        // Prevent sold quantity to be set as lower than the shipped quantity
        if ($quantity < $stockUnit->getShippedQuantity()) {
            throw new StockLogicException("The sold quantity can't be lower than the shipped quantity.");
        }

        $stockUnit->setSoldQuantity($quantity);

        $this->unitManager->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateShipped(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getShippedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected shipped quantity.");
        }

        // Prevent shipped quantity to be set as greater than the sold or received quantity
        if ($quantity > $stockUnit->getSoldQuantity()) {
            throw new StockLogicException("The shipped quantity can't be greater than the sold quantity.");
        }
        if ($quantity > $stockUnit->getReceivedQuantity() + $stockUnit->getAdjustedQuantity()) {
            throw new StockLogicException("The shipped quantity can't be greater than the sum (received + adjusted) quantity.");
        }

        $stockUnit->setShippedQuantity($quantity);

        $this->unitManager->persistOrRemove($stockUnit);
    }

    /**
     * @inheritdoc
     */
    public function updateEstimatedDateOfArrival(StockUnitInterface $stockUnit, \DateTime $date = null)
    {
        if ($date != $stockUnit->getEstimatedDateOfArrival()) {
            $stockUnit->setEstimatedDateOfArrival($date);

            $this->persistenceHelper->persistAndRecompute($stockUnit, true);
        }
    }

    /**
     * Checks stock unit overflow (sold > ordered + adjusted) and fixes assignments if needed.
     *
     * @param StockUnitInterface $stockUnit
     *
     * @return bool Whether assignment(s) has been moved.
     *
     * @throws StockLogicException
     */
    private function handleOverflow(StockUnitInterface $stockUnit)
    {
        // TODO Abort if stock unit is new ...

        // We don't care about shipped quantities because of the 'ordered > received > shipped' rule.
        $overflow = $stockUnit->getSoldQuantity()
            - $stockUnit->getOrderedQuantity()
            - $stockUnit->getAdjustedQuantity();

        // Abort if no overflow
        if (0 == $overflow) {
            return false;
        }

        $subject = $stockUnit->getSubject();

        // Negative case : too much sold quantity
        if (0 < $overflow) {
            // Try to move sold overflow to other pending/ready stock units
            // TODO prefer ready units with enough quantity
            $targetStockUnits = $this->unitResolver->findPendingOrReady($subject);
            foreach ($targetStockUnits as $targetStockUnit) {
                // Skip the stock unit we're applying
                if ($targetStockUnit === $stockUnit) {
                    continue;
                }

                $overflow -= $this->assignmentDispatcher->moveAssignments($stockUnit, $targetStockUnit, $overflow);

                if (0 == $overflow) {
                    break; // We're done dispatching sold quantity
                }
            }

            // Try to move sold overflow to a linkable stock unit
            if (null !== $targetStockUnit = $this->unitResolver->findLinkable($subject)) {
                $overflow -= $this->assignmentDispatcher->moveAssignments($stockUnit, $targetStockUnit, $overflow);
            }

            // Move sold overflow to a new stock unit
            if (0 < $overflow) {
                $newStockUnit = $this->unitResolver->createBySubject($subject);

                // Pre persist stock unit
                $this->persistenceHelper->persistAndRecompute($newStockUnit, false);

                $overflow -= $this->assignmentDispatcher->moveAssignments($stockUnit, $newStockUnit, $overflow);
            }

            if (0 != $overflow) {
                throw new StockLogicException("Failed to fix stock unit sold quantity overflow.");
            }

            return true;
        }

        // Positive case : not enough sold quantity
        if (null !== $sourceUnit = $this->unitResolver->findLinkable($subject)) {
            if (0 != $this->assignmentDispatcher->moveAssignments($sourceUnit, $stockUnit, -$overflow)) {
                return true;
            }
        }

        return false;
    }
}
