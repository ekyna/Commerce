<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Overflow\OverflowHandlerInterface;
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
     * @var OverflowHandlerInterface
     */
    protected $overflowHandler;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface         $persistenceHelper
     * @param StockUnitResolverInterface         $unitResolver
     * @param StockUnitManagerInterface          $unitManager
     * @param OverflowHandlerInterface $overflowHandler
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitResolverInterface $unitResolver,
        StockUnitManagerInterface $unitManager,
        OverflowHandlerInterface $overflowHandler
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->unitResolver = $unitResolver;
        $this->unitManager = $unitManager;
        $this->overflowHandler = $overflowHandler;
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

        if ($this->overflowHandler->handle($stockUnit)) {
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

        if ($quantity + $stockUnit->getReceivedQuantity() < $stockUnit->getShippedQuantity()) {
            throw new StockLogicException("Unexpected adjusted quantity.");
        }

        $stockUnit->setAdjustedQuantity($quantity);

        if ($this->overflowHandler->handle($stockUnit)) {
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
}
