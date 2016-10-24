<?php

namespace Ekyna\Component\Commerce\Shipment\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentItemInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
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
     * @var ResourceEventDispatcherInterface
     */
    protected $dispatcher;


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
     * Sets the resource event dispatcher.
     *
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function setDispatcher(ResourceEventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Insert event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onInsert(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        $this->dispatchShipmentContentChangeEvent($shipmentItem->getShipment());
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        if ($this->persistenceHelper->isChanged($shipmentItem, 'quantity')) {
            $this->dispatchShipmentContentChangeEvent($shipmentItem->getShipment());
        }
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        $this->dispatchShipmentContentChangeEvent($shipmentItem->getShipment());
    }

    /**
     * Pre update event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreUpdate(ResourceEventInterface $event)
    {
        /*$shipmentItem = $this->getShipmentItemFromEvent($event);

        $shipment = $shipmentItem->getShipment();
        // TODO assert updatable state
        if (!in_array($shipment->getState(), ShipmentStates::getDeletableStates())) {
            throw new IllegalOperationException();
        }*/
    }

    /**
     * Pre delete event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreDelete(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        $shipment = $shipmentItem->getShipment();

        if (!in_array($shipment->getState(), ShipmentStates::getDeletableStates())) {
            throw new IllegalOperationException();
        }
    }

    /**
     * Updates the relative stock and persist it if needed.
     *
     * @param ShipmentItemInterface $item
     */
    protected function updateStock(ShipmentItemInterface $item)
    {

    }

    /**
     * Dispatches the shipment content change event.
     *
     * @param ShipmentInterface $shipment
     */
    abstract protected function dispatchShipmentContentChangeEvent(ShipmentInterface $shipment);

    /**
     * Returns the shipment item from the event.
     *
     * @param ResourceEventInterface $event
     *
     * @return ShipmentItemInterface
     * @throws InvalidArgumentException
     */
    abstract protected function getShipmentItemFromEvent(ResourceEventInterface $event);
}
