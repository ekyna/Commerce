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
        } elseif (0 >= $quantity) {
            throw new InvalidArgumentException("Unexpected ordered quantity.");
        }

        $stockUnit->setOrderedQuantity($quantity);

        // Prevent quantity to be set as lower than delivered quantity
        if ($stockUnit->getOrderedQuantity() < $stockUnit->getDeliveredQuantity()) {
            throw new InvalidArgumentException("The ordered quantity can't be lower than the delivered quantity.");
        }

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }

    /**
     * @inheritdoc
     */
    public function updateDelivered(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getDeliveredQuantity() + $quantity;
        } elseif (0 >= $quantity) {
            throw new InvalidArgumentException("Unexpected delivered quantity.");
        }

        $stockUnit->setDeliveredQuantity($quantity);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }

    /**
     * @inheritdoc
     */
    public function updateShipped(StockUnitInterface $stockUnit, $quantity, $relative = true)
    {
        if ($relative) {
            $quantity = $stockUnit->getShippedQuantity() + $quantity;
        } elseif (0 >= $quantity) {
            throw new InvalidArgumentException("Unexpected shipped quantity.");
        }

        $stockUnit->setShippedQuantity($quantity);

        $this->persistenceHelper->persistAndRecompute($stockUnit, true);
    }

    /**
     * @inheritdoc
     */
    public function updateNetPrice(StockUnitInterface $stockUnit, $netPrice)
    {
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
