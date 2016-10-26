<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderShipmentEvents;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentItemInterface;
//use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentItemListener;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderShipmentItemListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentItemListener extends AbstractShipmentItemListener
{
    /**
     * Pre create event handler.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    public function onPreCreate(ResourceEventInterface $event)
    {
        if ($event->getHard()) {
            return;
        }

        //$this->throwIllegalOperationIfOrderIsCompleted($event);
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
        if ($event->getHard()) {
            return;
        }

        parent::onPreUpdate($event);

        //$this->throwIllegalOperationIfOrderIsCompleted($event);
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
        if ($event->getHard()) {
            return;
        }

        parent::onPreDelete($event);

        //$this->throwIllegalOperationIfOrderIsCompleted($event);
    }

    /**
     * Throws an illegal operation exception if the order is completed.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
//    private function throwIllegalOperationIfOrderIsCompleted(ResourceEventInterface $event)
//    {
//        $item = $this->getShipmentItemFromEvent($event);
//        /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
//        $order = $item->getShipment()->getSale();
//
//        // Stop sale is completed.
//        if ($order->getState() === OrderStates::STATE_COMPLETED) {
//            throw new IllegalOperationException(); // TODO reason message
//        }
//    }

    /**
     * @inheritdoc
     */
    protected function scheduleShipmentContentChangeEvent(ShipmentInterface $shipment)
    {
        $this->persistenceHelper->scheduleEvent(OrderShipmentEvents::CONTENT_CHANGE, $shipment);
    }

    /**
     * @inheritdoc
     */
    protected function getShipmentItemFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderShipmentItemInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentItemInterface");
        }

        return $resource;
    }

}
