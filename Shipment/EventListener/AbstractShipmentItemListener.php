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
     * @param StockUnitAssignerInterface $assigner
     */
    public function setStockUnitAssigner(StockUnitAssignerInterface $assigner)
    {
        $this->stockUnitAssigner = $assigner;
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

        $this->scheduleShipmentContentChangeEvent($shipment);

        // If shipment state is stockable
        if (Model\ShipmentStates::isStockableState($shipment->getState())) {
            // Assign shipment item to stock units
            $this->stockUnitAssigner->assignShipmentItem($item);
        }
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
        if (!empty($stateCs = $this->persistenceHelper->getChangeSet($shipment, 'state'))) {
            // If shipment just did a stockable state transition
            if (
                Model\ShipmentStates::hasChangedFromStockable($stateCs) ||
                Model\ShipmentStates::hasChangedToStockable($stateCs) ||
                Model\ShipmentStates::hasChangedFromPreparation($stateCs, true) ||
                Model\ShipmentStates::hasChangedToPreparation($stateCs, true)
            ) {
                // Abort (done by the shipment listener)
                return;
            }
        }

        // Abort if shipment is not in a stockable state
        if (!Model\ShipmentStates::isStockableState($shipment->getState())) {
            return;
        }

        // Apply shipment item to stock units
        $this->stockUnitAssigner->applyShipmentItem($item);

        $this->scheduleShipmentContentChangeEvent($shipment);
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $item = $this->getShipmentItemFromEvent($event);

        // Get shipment from change set if null
        if (null === $shipment = $item->getShipment()) {
            $shipment = $this->persistenceHelper->getChangeSet($item, 'shipment')[0];
        }

        // If shipment state has changed to stockable
        $stateCs = $this->persistenceHelper->getChangeSet($shipment, 'state');
        if (!empty($stateCs) && Model\ShipmentStates::hasChangedToStockable($stateCs)) {
            // Abort (item was not assigned)
            return;
        }

        // If shipment is (or was) in a stockable state
        if (
            Model\ShipmentStates::isStockableState($shipment->getState()) ||
            (!empty($stateCs) && Model\ShipmentStates::hasChangedFromStockable($stateCs))
        ) {
            // Detach shipment item to stock units
            $this->stockUnitAssigner->detachShipmentItem($item);
        }

        $this->scheduleShipmentContentChangeEvent($shipment);
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
