<?php

namespace Ekyna\Component\Commerce\Stock\Updater;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
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
    private $persistenceHelper;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     */
    public function __construct(PersistenceHelperInterface $persistenceHelper)
    {
        $this->persistenceHelper = $persistenceHelper;
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
            throw new InvalidArgumentException("Unexpected ordered quantity.");
        }

        // Prevent ordered quantity to be set as lower than the delivered quantity
        if ($quantity < $stockUnit->getDeliveredQuantity()) {
            throw new InvalidArgumentException("The ordered quantity can't be lower than the delivered quantity.");
        }

        $stockUnit->setOrderedQuantity($quantity);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }

    /**
     * @inheritdoc
     */
    public function updateDelivered(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getDeliveredQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new InvalidArgumentException("Unexpected delivered quantity.");
        }

        // Prevent delivered quantity to be set as greater than the ordered quantity
        if ($quantity > $stockUnit->getOrderedQuantity()) {
            throw new InvalidArgumentException("The delivered quantity can't be greater than the ordered quantity.");
        }

        $stockUnit->setDeliveredQuantity($quantity);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }

    /**
     * @inheritdoc
     */
    public function updateReserved(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getReservedQuantity() + $quantity;
        }
        if (0 > $quantity) {
            throw new InvalidArgumentException("Unexpected reserved quantity.");
        }

        // Prevent reserved quantity to be set as lower than the shipped quantity
        if ($quantity < $stockUnit->getShippedQuantity()) {
            throw new InvalidArgumentException("The reserved quantity can't be lower than the shipped quantity.");
        }

        $stockUnit->setReservedQuantity($quantity);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
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
            throw new InvalidArgumentException("Unexpected shipped quantity.");
        }

        // Prevent shipped quantity to be set as greater than the reserved quantity
        if ($quantity > $stockUnit->getReservedQuantity()) {
            throw new InvalidArgumentException("The shipped quantity can't be greater than the reserved quantity.");
        }

        $stockUnit->setShippedQuantity($quantity);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }

    /**
     * @inheritdoc
     */
    public function updateNetPrice(StockUnitInterface $stockUnit, $netPrice)
    {
        if (0 > $netPrice) {
            throw new InvalidArgumentException("Unexpected net price.");
        }

        if ($netPrice != $stockUnit->getNetPrice()) {
            $stockUnit->setNetPrice($netPrice);

            $this->persistenceHelper->persistAndRecompute($stockUnit, true);

            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function updateEstimatedDateOfArrival(StockUnitInterface $stockUnit, \DateTime $date)
    {
        if ($date != $stockUnit->getEstimatedDateOfArrival()) {
            $stockUnit->setEstimatedDateOfArrival($date);

            $this->persistenceHelper->persistAndRecompute($stockUnit, true);

            return true;
        }

        return false;
    }
}
