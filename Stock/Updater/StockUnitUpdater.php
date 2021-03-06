<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\StockLogicException;
use Ekyna\Component\Commerce\Stock\Manager\StockUnitManagerInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface as Unit;
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
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockUnitResolverInterface $unitResolver
     * @param StockUnitManagerInterface  $unitManager
     * @param OverflowHandlerInterface   $overflowHandler
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
    public function updateOrdered(Unit $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            // Turn into absolute quantity
            $quantity = $unit->getOrderedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected ordered quantity.");
        }

        // Prevent ordered quantity to be set as lower than the received quantity
        if ($quantity < $unit->getReceivedQuantity()) {
            throw new StockLogicException("The ordered quantity can't be lower than the received quantity.");
        }

        $unit->setOrderedQuantity($quantity);

        if ($this->overflowHandler->handle($unit)) {
            // Stock unit persistence has been made by assignment dispatcher.
            return;
        }

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateReceived(Unit $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            // Turn into absolute quantity
            $quantity = $unit->getReceivedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected received quantity.");
        }

        // Prevent received quantity to be set as greater than the ordered quantity
        if ($quantity > $unit->getOrderedQuantity()) {
            throw new StockLogicException("The received quantity can't be greater than the ordered quantity.");
        }

        // Prevent received quantity to be set as lower than the shipped + locked - adjusted quantity
        if ($quantity < $unit->getShippedQuantity() + $unit->getLockedQuantity() - $unit->getAdjustedQuantity()) {
            throw new StockLogicException(
                "The received quantity can't be lower than the sum of shipped and locked quantity minus adjusted quantity."
            );
        }

        $unit->setReceivedQuantity($quantity);

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateAdjusted(Unit $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            // Turn into absolute quantity
            $quantity = $unit->getAdjustedQuantity() + $quantity;
        }

        // Prevent adjusted quantity to be set as lower than the shipped + locked - received quantity
        if ($quantity < $unit->getShippedQuantity() + $unit->getLockedQuantity() - $unit->getReceivedQuantity()) {
            throw new StockLogicException(
                "The adjusted quantity can't be lower than the sum of shipped and locked quantity minus received quantity."
            );
        }

        $unit->setAdjustedQuantity($quantity);

        if ($this->overflowHandler->handle($unit)) {
            // Stock unit persistence has been made by assignment dispatcher.
            return;
        }

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateSold(Unit $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            // Turn into absolute quantity
            $quantity = $unit->getSoldQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected sold quantity.");
        }

        // Prevent sold quantity to be set as lower than the shipped + locked quantity
        if ($quantity < $unit->getShippedQuantity() + $unit->getLockedQuantity()) {
            throw new StockLogicException(
                "The sold quantity can't be lower than the sum of shipped and locked quantity."
            );
        }

        $unit->setSoldQuantity($quantity);

        // TODO Use overflow handler ?

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateShipped(Unit $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            // Turn into absolute quantity
            $quantity = $unit->getShippedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected shipped quantity.");
        }

        // Prevent shipped quantity to be set as greater than the received + adjusted - locked quantity
        if ($quantity > $unit->getReceivedQuantity() + $unit->getAdjustedQuantity() - $unit->getLockedQuantity()) {
            throw new StockLogicException(
                "The shipped quantity can't be greater than the sum of received and adjusted quantity minus locked quantity."
            );
        }

        // Prevent shipped quantity to be set as greater than the sold - locked quantity
        if ($quantity > $unit->getSoldQuantity() - $unit->getLockedQuantity()) {
            throw new StockLogicException(
                "The shipped quantity can't be greater than the sold quantity minus the locked quantity."
            );
        }

        $unit->setShippedQuantity($quantity);

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateLocked(Unit $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            // Turn into absolute quantity
            $quantity = $unit->getLockedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected locked quantity.");
        }

        // Prevent locked quantity to be set as greater than the received + adjusted - shipped quantity
        if ($quantity > $unit->getReceivedQuantity() + $unit->getAdjustedQuantity() - $unit->getShippedQuantity()) {
            throw new StockLogicException(
                "The locked quantity can't be greater than the sum of received and adjusted quantity minus the shipped quantity."
            );
        }
        // Prevent locked quantity to be set as greater than the sold - shipped quantity
        if ($quantity > $unit->getSoldQuantity() - $unit->getShippedQuantity()) {
            throw new StockLogicException(
                "The locked quantity can't be greater than the sold quantity minus the shipped quantity."
            );
        }

        $unit->setLockedQuantity($quantity);

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateEstimatedDateOfArrival(Unit $unit, \DateTime $date = null): void
    {
        if ($date != $unit->getEstimatedDateOfArrival()) {
            $unit->setEstimatedDateOfArrival($date);

            $this->persistenceHelper->persistAndRecompute($unit, true);
        }
    }

    /**
     * @inheritdoc
     */
    public function updateNetPrice(Unit $unit, float $price): void
    {
        if ($price != $unit->getNetPrice()) {
            $unit->setNetPrice($price);

            $this->persistenceHelper->persistAndRecompute($unit, true);
        }
    }

    /**
     * @inheritdoc
     */
    public function updateShippingPrice(Unit $unit, float $price): void
    {
        if ($price != $unit->getShippingPrice()) {
            $unit->setShippingPrice($price);

            $this->persistenceHelper->persistAndRecompute($unit, true);
        }
    }
}
