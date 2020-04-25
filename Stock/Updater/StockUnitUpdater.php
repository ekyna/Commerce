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
    public function updateOrdered(StockUnitInterface $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
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
    public function updateReceived(StockUnitInterface $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            $quantity = $unit->getReceivedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected received quantity.");
        }

        // Prevent received quantity to be set as greater than the ordered quantity
        if ($quantity > $unit->getOrderedQuantity()) {
            throw new StockLogicException("The received quantity can't be greater than the ordered quantity.");
        }

        $unit->setReceivedQuantity($quantity);

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateAdjusted(StockUnitInterface $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            $quantity = $unit->getAdjustedQuantity() + $quantity;
        }

        if ($quantity + $unit->getReceivedQuantity() < $unit->getShippedQuantity()) {
            throw new StockLogicException("Unexpected adjusted quantity.");
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
    public function updateSold(StockUnitInterface $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            $quantity = $unit->getSoldQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected sold quantity.");
        }

        // Prevent sold quantity to be set as lower than the shipped quantity
        if ($quantity < $unit->getShippedQuantity()) {
            throw new StockLogicException("The sold quantity can't be lower than the shipped quantity.");
        }

        $unit->setSoldQuantity($quantity);

        // TODO Use overflow handler ?

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateShipped(StockUnitInterface $unit, float $quantity, bool $relative = true): void
    {
        if ($relative) {
            $quantity = $unit->getShippedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new StockLogicException("Unexpected shipped quantity.");
        }

        // Prevent shipped quantity to be set as greater than the sold or received quantity
        if ($quantity > $unit->getSoldQuantity()) {
            throw new StockLogicException("The shipped quantity can't be greater than the sold quantity.");
        }
        if ($quantity > $unit->getReceivedQuantity() + $unit->getAdjustedQuantity()) {
            throw new StockLogicException("The shipped quantity can't be greater than the sum (received + adjusted) quantity.");
        }

        $unit->setShippedQuantity($quantity);

        $this->unitManager->persistOrRemove($unit);
    }

    /**
     * @inheritdoc
     */
    public function updateEstimatedDateOfArrival(StockUnitInterface $unit, \DateTime $date = null): void
    {
        if ($date != $unit->getEstimatedDateOfArrival()) {
            $unit->setEstimatedDateOfArrival($date);

            $this->persistenceHelper->persistAndRecompute($unit, true);
        }
    }

    /**
     * @inheritdoc
     */
    public function updateNetPrice(StockUnitInterface $unit, float $price): void
    {
        if ($price != $unit->getNetPrice()) {
            $unit->setNetPrice($price);

            $this->persistenceHelper->persistAndRecompute($unit, true);
        }
    }

    /**
     * @inheritdoc
     */
    public function updateShippingPrice(StockUnitInterface $unit, float $price): void
    {
        if ($price != $unit->getShippingPrice()) {
            $unit->setShippingPrice($price);

            $this->persistenceHelper->persistAndRecompute($unit, true);
        }
    }
}
