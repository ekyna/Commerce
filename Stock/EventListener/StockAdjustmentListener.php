<?php

namespace Ekyna\Component\Commerce\Stock\EventListener;

use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentInterface;
use Ekyna\Component\Commerce\Stock\Model\StockAdjustmentReasons;
use Ekyna\Component\Commerce\Stock\Updater\StockUnitUpdaterInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class StockAdjustmentListener
 * @package Ekyna\Component\Commerce\Stock\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockAdjustmentListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitUpdaterInterface
     */
    protected $stockUnitUpdater;


    /**
     * Constructor.
     *
     * @param PersistenceHelperInterface $persistenceHelper
     * @param StockUnitUpdaterInterface  $stockUnitUpdater
     */
    public function __construct(
        PersistenceHelperInterface $persistenceHelper,
        StockUnitUpdaterInterface $stockUnitUpdater
    ) {
        $this->persistenceHelper = $persistenceHelper;
        $this->stockUnitUpdater = $stockUnitUpdater;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $stockAdjustment = $this->getStockAdjustmentFromEvent($event);

        if (null === $stockUnit = $stockAdjustment->getStockUnit()) {
            throw new RuntimeException("Stock unit must be set at this point.");
        }

        $quantity = StockAdjustmentReasons::isDebitReason($stockAdjustment->getReason())
            ? -$stockAdjustment->getQuantity()
            : $stockAdjustment->getQuantity();

        $this->stockUnitUpdater->updateAdjusted($stockUnit, $quantity, true);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $stockAdjustment = $this->getStockAdjustmentFromEvent($event);

        if (null === $stockUnit = $stockAdjustment->getStockUnit()) {
            throw new RuntimeException("Stock unit must be set at this point.");
        }

        $cs = $this->persistenceHelper->getChangeSet($stockAdjustment);

        if (isset($cs['reason']) && ($cs['reason'][0] != $cs['reason'][1])) {
            // (Default) From DEBIT to CREDIT
            if (isset($cs['quantity'])) {
                $delta = $cs['quantity'][0] + $cs['quantity'][1];
            } else {
                $delta = 2 * $stockAdjustment->getQuantity();
            }
            if (
                !StockAdjustmentReasons::isDebitReason($cs['reason'][0])
                && StockAdjustmentReasons::isDebitReason($cs['reason'][1])
            ) {
                // From CREDIT to DEBIT
                $delta = - $delta;
            }
        } elseif (isset($cs['quantity'])) {
            // OLD quantity - NEW quantity
            $delta = $cs['quantity'][1] - $cs['quantity'][0];

            // Opposite delta if debit
            if (StockAdjustmentReasons::isDebitReason($stockAdjustment->getReason())) {
                $delta = -$delta;
            }
        } else {
            return;
        }

        $this->stockUnitUpdater->updateAdjusted($stockUnit, $delta, true);
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $stockAdjustment = $this->getStockAdjustmentFromEvent($event);

        if (null === $stockUnit = $stockAdjustment->getStockUnit()) {
            throw new RuntimeException("Stock unit must be set at this point.");
        }

        $quantity = StockAdjustmentReasons::isDebitReason($stockAdjustment->getReason())
            ? $stockAdjustment->getQuantity()
            : -$stockAdjustment->getQuantity();

        $this->stockUnitUpdater->updateAdjusted($stockUnit, $quantity, true);
    }

    /**
     * Returns the stock adjustment from the resource event.
     *
     * @param ResourceEventInterface $event
     *
     * @return StockAdjustmentInterface
     */
    protected function getStockAdjustmentFromEvent(ResourceEventInterface $event)
    {
        $stockAdjustment = $event->getResource();

        if (!$stockAdjustment instanceof StockAdjustmentInterface) {
            throw new InvalidArgumentException("Expected instance of " . StockAdjustmentInterface::class);
        }

        return $stockAdjustment;
    }
}