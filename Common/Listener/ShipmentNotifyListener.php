<?php

namespace Ekyna\Component\Commerce\Common\Listener;

use Ekyna\Component\Commerce\Common\Model\NotificationTypes;
use Ekyna\Component\Commerce\Common\Model\SaleInterface;
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
        if ($shipment->isReturn()) {
            // If state is 'PENDING'
            if ($shipment->getState() === ShipmentStates::STATE_PENDING) {
                // Abort if shipment state has not changed for 'PENDING'
                if (!$this->didStateChangeTo($shipment, ShipmentStates::STATE_PENDING)) {
                    return;
                }

                $order = $shipment->getOrder();

                // Abort if sale has notification of type 'RETURN_PENDING' with same shipment number
                if ($this->hasNotification($order, NotificationTypes::RETURN_PENDING, $shipment->getNumber())) {
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

                $order = $shipment->getOrder();

                // Abort if sale has notification of type 'RETURN_RECEIVED' with same shipment number
                if ($this->hasNotification($order, NotificationTypes::RETURN_RECEIVED, $shipment->getNumber())) {
                    return;
                }

                $this->notify(NotificationTypes::RETURN_RECEIVED, $shipment);
            }

            return;
        }

        // Abort if shipment state has not changed for 'SHIPPED'
        if (!$this->didStateChangeTo($shipment, ShipmentStates::STATE_SHIPPED)) {
            return;
        }

        $order = $shipment->getOrder();

        // Abort if sale has notification of type 'SHIPMENT_SHIPPED' with same shipment number
        if ($this->hasNotification($order, NotificationTypes::SHIPMENT_SHIPPED, $shipment->getNumber())) {
            return;
        }
        // Abort if sale has notification of type 'SHIPMENT_PARTIAL' with same shipment number
        if ($this->hasNotification($order, NotificationTypes::SHIPMENT_SHIPPED, $shipment->getNumber())) {
            return;
        }

        $type = NotificationTypes::SHIPMENT_SHIPPED;
        if ($order->getShipmentState() !== ShipmentStates::STATE_COMPLETED) {
            $type = NotificationTypes::SHIPMENT_PARTIAL;
        }

        $this->notify($type, $shipment);
    }

    /**
     * Returns whether the sae has a notification with the given type and shipment number.
     *
     * @param SaleInterface $sale
     * @param               $type
     * @param               $number
     *
     * @return bool
     */
    protected function hasNotification(SaleInterface $sale, $type, $number)
    {
        foreach ($sale->getNotifications() as $n) {
            if ($n->getType() !== $type) {
                continue;
            }

            if ($n->hasData('shipment') && $n->getData('shipment') === $number) {
                return true;
            }
        }

        return false;
    }
}
