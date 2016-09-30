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

        $shipment = $shipmentItem->getShipment();

        /*if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($shipment);
        }*/

        //$this->dispatchShipmentContentChangeEvent($shipment);
    }

    /**
     * Update event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onUpdate(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        $shipment = $shipmentItem->getShipment();

        /*if (true || $changed) {
            $this->persistenceHelper->persistAndRecompute($shipment);
        }*/

        /*if ($this->persistenceHelper->isChanged($shipment, 'state')) {
            $this->dispatchShipmentContentChangeEvent($shipment);
        }*/
    }

    /**
     * Delete event handler.
     *
     * @param ResourceEventInterface $event
     */
    public function onDelete(ResourceEventInterface $event)
    {
        $shipmentItem = $this->getShipmentItemFromEvent($event);

        $shipment = $shipmentItem->getShipment();

        /*if ($this->persistenceHelper->isChanged($shipment, 'state')) {
            $this->dispatchShipmentContentChangeEvent($shipment);
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
