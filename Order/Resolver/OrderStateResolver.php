<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates as Pay;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates as Ship;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;

/**
 * Class OrderStateResolver
 * @package Ekyna\Component\Commerce\Order\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class OrderStateResolver extends AbstractSaleStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(StateSubjectInterface $order)
    {
        if (!$order instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface.");
        }

        $changed = false;
        $oldState = $order->getState();
        $newState = OrderStates::STATE_NEW;

        // Payments state
        $paymentState = $this->resolvePaymentsState($order);
        if ($paymentState != $order->getPaymentState()) {
            $order->setPaymentState($paymentState);
            $changed = true;
        }

        // Shipments state
        $shipmentState = $this->resolveShipmentsState($order);
        if ($shipmentState != $order->getShipmentState()) {
            $order->setShipmentState($shipmentState);
            $changed = true;
        }

        $outstanding = $this->resolveOutstanding($order, $paymentState);

        // Order states
        if ($order->hasItems()) {
            if (Pay::isPaidState($paymentState)) {
                if (Ship::isShippedState($shipmentState)) {
                    $newState = OrderStates::STATE_COMPLETED;
                } else {
                    $newState = OrderStates::STATE_ACCEPTED;
                }
            } elseif ($outstanding) {
                $newState = OrderStates::STATE_PENDING; // TODO ?
                if ($outstanding->isValid()) {
                    $newState = OrderStates::STATE_ACCEPTED;
                } elseif ($outstanding->isExpired()) {
                    $newState = OrderStates::STATE_OUTSTANDING;
                }
            } elseif ($paymentState == Pay::STATE_PENDING) {
                $newState = OrderStates::STATE_PENDING;
            } elseif ($paymentState == Pay::STATE_FAILED) {
                $newState = OrderStates::STATE_REFUSED;
            } elseif ($paymentState == Pay::STATE_REFUNDED) {
                $newState = OrderStates::STATE_REFUNDED;
            } elseif ($paymentState == Pay::STATE_CANCELLED) {
                $newState = OrderStates::STATE_CANCELLED;
            } else {
                $newState = OrderStates::STATE_NEW;
            }
        }

        // If the new order state is not stockable and order has at least one stockable shipment
        // Set the new state as ACCEPTED
        if (!OrderStates::isStockableState($newState) && $order->hasShipments()) {
            foreach ($order->getShipments() as $shipment) {
                if (ShipmentStates::isStockableState($shipment->getState())) {
                    $newState = OrderStates::STATE_ACCEPTED;
                    break;
                }
            }
        }

        if ($oldState != $newState) {
            $order->setState($newState);
            $changed = true;
        }

        return $changed;
    }

    /**
     * Resolves the global shipment state.
     *
     * @param OrderInterface $order
     *
     * @return string
     */
    private function resolveShipmentsState(OrderInterface $order)
    {
        if (!$order->requiresShipment()) {
            return Ship::STATE_NONE;
        }

        if (!$order->hasItems()) {
            return Ship::STATE_PENDING;
        }

        $shipments = $order->getShipments();
        $quantities = [];

        // Build ordered quantities.
        foreach ($order->getItems() as $item) {
            $this->buildOrderedQuantities($item, $quantities);
        }

        // Build shipped quantities.
        foreach ($shipments as $shipment) {
            // Ignore if shipment is not shipped or completed
            if (!Ship::isStockableState($shipment->getState())) {
                continue;
            }

            $key = $shipment->isReturn() ? 'returned' : 'shipped';

            foreach ($shipment->getItems() as $shipmentItem) {
                $orderItem = $shipmentItem->getSaleItem();
                if (!isset($quantities[$orderItem->getId()])) {
                    throw new RuntimeException("Shipment item / Sale item miss match.");
                }

                $quantities[$orderItem->getId()][$key] += $shipmentItem->getQuantity();
            }
        }

        $partialCount = $shippedCount = $returnedCount = 0;

        foreach ($quantities as $q) {
            // If returned quantity equals max of ordered or shipped, item is fully returned
            if (max($q['ordered'], $q['shipped']) == $q['returned']) {
                $returnedCount++;
                continue;
            }
            $delta = $q['shipped'] - $q['returned'];

            // Else if shipped quantity minus returned equals ordered, item is fully shipped
            if ($q['ordered'] == $delta) {
                $shippedCount++;
            }
            // Else if shipped minus returned is greater than zero, item is partially shipped
            elseif (0 < $delta) {
                $partialCount++;
            }
        }

        // If all fully returned
        if ($returnedCount == count($quantities)) {
            return Ship::STATE_RETURNED;

        }
        // Else if all fully shipped
        elseif ($shippedCount == count($quantities)) {
            // Watch for non completed shipment
            foreach ($shipments as $shipment) {
                if ($shipment->getState() != Ship::STATE_COMPLETED) {
                    return Ship::STATE_SHIPPED;
                }
            }
            // All clear !
            return Ship::STATE_COMPLETED;
        }
        // Else if some partially shipped
        elseif (0 < $partialCount) {
            return Ship::STATE_PARTIAL;
        }

        return Ship::STATE_PENDING;
    }

    /**
     * Builds the quantity (order item / shipments items) array.
     *
     * @param SaleItemInterface $item
     * @param array             $quantities
     */
    private function buildOrderedQuantities(SaleItemInterface $item, array &$quantities)
    {
        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildOrderedQuantities($child, $quantities);
            }
        } else {
            $quantities[$item->getId()] = array(
                'ordered' => $item->getTotalQuantity(),
                'shipped' => 0,
                'returned' => 0,
            );
        }
    }
}
