<?php

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Ekyna\Component\Commerce\Exception;
use Ekyna\Component\Commerce\Shipment\Model;
use Ekyna\Component\Commerce\Stock\Assigner\StockUnitAssignerInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

/**
 * Class AbstractShipmentItemListener
 * @package Ekyna\Component\Commerce\Shipment\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractShipmentItemListener
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
        $item = $this->getShipmentItemFromEvent($event);
        $shipment = $item->getShipment();

        // If shipment state is stockable (TODO take care of returned state)
        if (Model\ShipmentStates::isStockableState($shipment->getState())) {
            if ($shipment->isReturn()) {
                // Detach shipment item to stock units
                $this->stockUnitAssigner->detachShipmentItem($item);
            } else {
                // Assign shipment item to stock units
                $this->stockUnitAssigner->assignShipmentItem($item);
            }
        }

        $this->scheduleShipmentContentChangeEvent($shipment);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $item = $this->getShipmentItemFromEvent($event);
        $shipment = $item->getShipment();

        $this->preventSaleItemChange($item);

        // Check whether or not the stock impact has been made by the shipment listener
        if ($this->persistenceHelper->isChanged($shipment, 'state')) {
            $stateCs = $this->persistenceHelper->getChangeSet($shipment, 'state');

            // If shipment just did a stockable state transition
            if (
                Model\ShipmentStates::hasChangedFromStockable($stateCs) ||
                Model\ShipmentStates::hasChangedToStockable($stateCs)
            ) {
                // Abort (done by the shipment listener)
                return;
            }
        }

        // If shipment is in a stockable state and quantity has changed
        if (
            Model\ShipmentStates::isStockableState($shipment->getState()) &&
            $this->persistenceHelper->isChanged($item, 'quantity')
        ) {
            // Apply shipment item to stock units
            $this->stockUnitAssigner->applyShipmentItem($item);

            $this->scheduleShipmentContentChangeEvent($shipment);
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getShipmentItemFromEvent($event);
        $shipment = $item->getShipment();
        // TODO get shipment from change set if null ?

        //$this->preventOrderItemChange($item);

        // If shipment is in a stockable state and quantity has changed
        // TODO Or shipment was in stockable state (watch state change set) ?
        if (Model\ShipmentStates::isStockableState($shipment->getState())) {
            if ($shipment->isReturn()) {
                // Assign shipment item to stock units
                $this->stockUnitAssigner->assignShipmentItem($item);
            } else {
                // Detach shipment item to stock units
                $this->stockUnitAssigner->detachShipmentItem($item);
            }
        }

        $this->scheduleShipmentContentChangeEvent($shipment);
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws Exception\IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        $shipment = $shipmentItem->getShipment();

        if (!in_array($shipment->getState(), Model\ShipmentStates::getDeletableStates())) {
            throw new Exception\IllegalOperationException(); // TODO reason message
        }
    }

    /**
     * Prevents the sale item from changing.
     *
     * @param Model\ShipmentItemInterface $item
     */
    abstract protected function preventSaleItemChange(Model\ShipmentItemInterface $item);

    /**
     * Schedules the shipment content change event.
     *
     * @param Model\ShipmentInterface $shipment
     */
    abstract protected function scheduleShipmentContentChangeEvent(Model\ShipmentInterface $shipment);

    /**
     * Returns the shipment item from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return Model\ShipmentItemInterface
     * @throws Exception\InvalidArgumentException
     */
    abstract protected function getShipmentItemFromEvent(ResourceEventInterface $event);
}
