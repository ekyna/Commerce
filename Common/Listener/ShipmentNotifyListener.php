<?php

namespace Ekyna\Component\Commerce\Common\Listener;

use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Order\Model\OrderShipmentInterface;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class ShipmentNotifyListener
 * @package Ekyna\Component\Commerce\Common\Listener
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentNotifyListener extends AbstractNotifyListener
{
    /**
     * Post persist event handler.
     *
     * @param OrderShipmentInterface $shipment
     */
    public function postPersist(OrderShipmentInterface $shipment)
    {
        $this->watch($shipment);
    }

    /**
     * Post update event handler.
     *
     * @param OrderShipmentInterface $shipment
     */
    public function postUpdate(OrderShipmentInterface $shipment)
    {
        $this->watch($shipment);
    }

    /**
     * Shipment watcher.
     *
     * @param OrderShipmentInterface $shipment
     */
    protected function watch(OrderShipmentInterface $shipment)
    {
        $order = $shipment->getOrder();

        // Abort if notify disabled
        if (!$order->isAutoNotify()) {
            return;
        }

        $number = $shipment->getNumber();

        if ($shipment->isReturn()) {
            // If state is 'PENDING'
            if ($shipment->getState() === ShipmentStates::STATE_PENDING) {
                // Abort if shipment state has not changed for 'PENDING'
                if (!$this->didStateChangeTo($shipment, ShipmentStates::STATE_PENDING)) {
                    return;
                }

                // Abort if sale has notification of type 'RETURN_PENDING' with same shipment number
                if ($this->hasNotification($order, NotificationTypes::RETURN_PENDING, 'shipment', $number)) {
                    return;
                }

                $this->notify(NotificationTypes::RETURN_PENDING, $shipment);

                return;
            }

            // Else if state has changed for 'RETURNED'
            if ($shipment->getState() === ShipmentStates::STATE_RETURNED) {
                // Abort if shipment state has not changed for 'RETURNED'
                if (!$this->didStateChangeTo($shipment, ShipmentStates::STATE_RETURNED)) {
                    return;
                }

                // Abort if sale has notification of type 'RETURN_RECEIVED' with same shipment number
                if ($this->hasNotification($order, NotificationTypes::RETURN_RECEIVED, 'shipment', $number)) {
                    return;
                }

                $this->notify(NotificationTypes::RETURN_RECEIVED, $shipment);
            }

            return;
        }

        // Abort if sale has notification of type 'SHIPMENT_READY' with same shipment number
        if ($this->hasNotification($order, NotificationTypes::SHIPMENT_READY, 'shipment', $number)) {
            return;
        }

        // If shipment is 'READY' (in store gateway)
        if ($shipment->getState() === ShipmentStates::STATE_READY) {
            // Abort if shipment state has not changed for 'READY'
            if (!$this->didStateChangeTo($shipment, ShipmentStates::STATE_READY)) {
                return;
            }

            $this->notify(NotificationTypes::SHIPMENT_READY, $shipment);

            return;
        }

        // Abort if shipment state has not changed for 'SHIPPED'
        if (!$this->didStateChangeTo($shipment, ShipmentStates::STATE_SHIPPED)) {
            return;
        }

        // Abort if sale has notification of type 'SHIPMENT_SHIPPED' with same shipment number
        if ($this->hasNotification($order, NotificationTypes::SHIPMENT_COMPLETE, 'shipment', $number)) {
            return;
        }
        // Abort if sale has notification of type 'SHIPMENT_PARTIAL' with same shipment number
        if ($this->hasNotification($order, NotificationTypes::SHIPMENT_PARTIAL, 'shipment', $number)) {
            return;
        }

        $type = NotificationTypes::SHIPMENT_COMPLETE;
        if ($order->getShipmentState() !== ShipmentStates::STATE_COMPLETED) {
            $type = NotificationTypes::SHIPMENT_PARTIAL;
        }

        $this->notify($type, $shipment);
    }
}
