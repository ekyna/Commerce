<?php

namespace Ekyna\Component\Commerce\Credit\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Credit\Model;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractCreditItemListener
 * @package Ekyna\Component\Commerce\Credit\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractCreditItemListener
{
    /**
     * @var PersistenceHelperInterface
     */
    protected $persistenceHelper;

    /**
     * @var StockUnitAssignerInterface
     */
    protected $stockUnitAssigner;


    /**
     * Sets the persistence helper.
     *
     * @param PersistenceHelperInterface $helper
     */
    public function setPersistenceHelper(PersistenceHelperInterface $helper)
    {
        $this->persistenceHelper = $helper;
    }

    /**
     * Sets the stock assigner.
     *
     * @param StockUnitAssignerInterface $stockUnitAssigner
     */
    public function setStockUnitAssigner(StockUnitAssignerInterface $stockUnitAssigner)
    {
        $this->stockUnitAssigner = $stockUnitAssigner;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $item = $this->getCreditItemFromEvent($event);
        $credit = $item->getCredit();

        // Assign credit item to stock units
        $this->stockUnitAssigner->assignCreditItem($item);

        $this->scheduleCreditContentChangeEvent($credit);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $item = $this->getCreditItemFromEvent($event);
        $credit = $item->getCredit();

        $this->preventSaleItemOrShipmentItemChange($item);

        $doApply = true;
        $sale = $credit->getSale();
        if ($this->persistenceHelper->isChanged($sale, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($sale, 'state');

            // If order just did a stockable state transition
            if (
                OrderStates::hasChangedToStockable($stateCs) ||
                OrderStates::hasChangedFromStockable($stateCs)
            ) {
                // Prevent assignments update (handled by the order listener)
                $doApply = false;
            }
        }

        // If order is in stockable state and order item quantity has changed
        if ($doApply && OrderStates::isStockableState($sale->getState())) {
            if ($this->persistenceHelper->isChanged($item, 'quantity')) {
                $this->stockUnitAssigner->applyCreditItem($item);
            }
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getCreditItemFromEvent($event);
        $credit = $item->getCredit();

        // TODO get credit from change set if null ?

        // Detach credit item to stock units
        $this->stockUnitAssigner->detachCreditItem($item);

        $this->scheduleCreditContentChangeEvent($credit);
    }

    /**
     * Prevents the credit item's sale item or the credit item's shipment item from changing.
     *
     * @param Model\CreditItemInterface $item
     */
    abstract protected function preventSaleItemOrShipmentItemChange(Model\CreditItemInterface $item);

    /**
     * Schedules the credit content change event.
     *
     * @param Model\CreditInterface $credit
     */
    abstract protected function scheduleCreditContentChangeEvent(Model\CreditInterface $credit);

    /**
     * Returns the credit item from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\CreditItemInterface
     * @throws Exception\InvalidArgumentException
     */
    abstract protected function getCreditItemFromEvent(ResourceEventInterface $event);
}
