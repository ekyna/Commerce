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
    public function updateOrdered(StockUnitInterface $stockUnit, $quantity)
    {
        if (0 == $quantity) {
            throw new InvalidArgumentException("Please provide a non zero ordered quantity.");
        }

        $result = $stockUnit->getOrderedQuantity() + $quantity;

        if (0 > $result) {
            throw new InvalidArgumentException("Unexpected ordered quantity.");
        }

        $stockUnit->setOrderedQuantity($result);

        $this->persistenceHelper->persistAndRecompute($stockUnit);
        // TODO Do we need to dispatch STOCK_UNIT_CHANGE event ?
    }

    /**
     * @inheritdoc
     */
    public function updateEstimatedDateOfArrival(StockUnitInterface $stockUnit, \DateTime $date)
    {
        if ($date != $stockUnit->getEstimatedDateOfArrival()) {
            $stockUnit->setEstimatedDateOfArrival($date);

            $this->persistenceHelper->persistAndRecompute($stockUnit);
            // TODO Do we need to dispatch STOCK_UNIT_CHANGE event ?
        }
    }

    /**
     * @inheritdoc
     */
    public function updateDelivered(StockUnitInterface $stockUnit, $quantity)
    {
        if (0 == $quantity) {
            throw new InvalidArgumentException("Please provide a non zero delivered quantity.");
        }

        $result = $stockUnit->getDeliveredQuantity() + $quantity;

        if (0 > $result) {
            throw new InvalidArgumentException("Unexpected delivered quantity.");
        }

        $stockUnit->setDeliveredQuantity($result);

        $this->persistenceHelper->persistAndRecompute($stockUnit);
        // TODO Do we need to dispatch STOCK_UNIT_CHANGE event ?
    }

    /**
     * @inheritdoc
     */
    public function updateShipped(StockUnitInterface $stockUnit, $quantity)
    {
        if (0 == $quantity) {
            throw new InvalidArgumentException("Please provide a non zero shipped quantity.");
        }

        $result = $stockUnit->getShippedQuantity() + $quantity;

        if (0 > $result) {
            throw new InvalidArgumentException("Unexpected shipped quantity.");
        }

        $stockUnit->setShippedQuantity($result);

        $this->persistenceHelper->persistAndRecompute($stockUnit);
        // TODO Do we need to dispatch STOCK_UNIT_CHANGE event ?
    }
}
