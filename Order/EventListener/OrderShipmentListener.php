<?php

namespace Ekyna\Component\Commerce\Order\EventListener;

use Ekyna\Component\Commerce\Common\Model\SaleInterface;
use Ekyna\Component\Commerce\Exception\IllegalOperationException;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Event\OrderEvents;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Shipment\EventListener\AbstractShipmentListener;
use Ekyna\Component\Resource\Event\ResourceEventInterface;

/**
 * Class OrderShipmentListener
 * @package Ekyna\Component\Commerce\Order\EventListener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderShipmentListener extends AbstractShipmentListener
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

        $this->throwIllegalOperationIfOrderIsCompleted($event);
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

        $this->throwIllegalOperationIfOrderIsCompleted($event);
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

        $this->throwIllegalOperationIfOrderIsCompleted($event);
    }

    /**
     * Throws an illegal operation exception if the order is completed.
     *
     * @param ResourceEventInterface $event
     *
     * @throws IllegalOperationException
     */
    private function throwIllegalOperationIfOrderIsCompleted(ResourceEventInterface $event)
    {
        $shipment = $this->getShipmentFromEvent($event);
        /** @var \Ekyna\Component\Commerce\Order\Model\OrderInterface $order */
        $order = $shipment->getSale();

        // Stop sale is completed.
        if ($order->getState() === OrderStates::STATE_COMPLETED) {
            throw new IllegalOperationException(); // TODO reason message
        }
    }

    /**
     * @inheritdoc
     */
    protected function dispatchSaleContentChangeEvent(SaleInterface $sale)
    {
        $event = $this->dispatcher->createResourceEvent($sale);

        $this->dispatcher->dispatch(OrderEvents::CONTENT_CHANGE, $event);
    }

    /**
     * @inheritdoc
     */
    protected function getShipmentFromEvent(ResourceEventInterface $event)
    {
        $resource = $event->getResource();

        if (!$resource instanceof OrderShipmentInterface) {
            throw new InvalidArgumentException("Expected instance of OrderShipmentInterface");
        }

        return $resource;
    }
}
