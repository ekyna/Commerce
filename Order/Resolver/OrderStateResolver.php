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

        $oldState = $order->getState();
        $newState = OrderStates::STATE_NEW;

        $paymentState = $this->resolvePaymentsState($order);
        $shipmentState = $this->resolveShipmentsState($order);

        if ($order->hasItems()) {
            if ($paymentState === Pay::STATE_CAPTURED && in_array($shipmentState, Ship::getStockStates())) {
                $newState = OrderStates::STATE_COMPLETED;
            } elseif (in_array($paymentState, [Pay::STATE_PENDING, Pay::STATE_AUTHORIZED, Pay::STATE_CAPTURED])) {
                $newState = OrderStates::STATE_ACCEPTED;
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

        $changed = false;

        if ($paymentState != $order->getPaymentState()) {
            $order->setPaymentState($paymentState);
            $changed = true;
        }

        if ($shipmentState != $order->getShipmentState()) {
            $order->setShipmentState($shipmentState);
            $changed = true;
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
            if (!in_array($shipment->getState(), Ship::getStockStates(), true)) {
                continue;
            }

            foreach ($shipment->getItems() as $shipmentItem) {
                $orderItem = $shipmentItem->getSaleItem();
                if (!isset($quantities[$orderItem->getId()])) {
                    throw new RuntimeException("Shipment item / Sale item miss match.");
                }

                $quantities[$orderItem->getId()]['shipped'] += $shipmentItem->getQuantity();
            }
        }

        $doneCount = $partialCount = 0;

        foreach ($quantities as $q) {
            // If shipped quantity equals ordered quantity : increment done count
            if ($q['shipped'] == $q['ordered']) {
                $doneCount++;
                continue;
            }
            // If shipped quantity is greater than zero : increment partial count
            if (0 < $q['shipped']) {
                $partialCount++;
                continue;
            }
        }

        // If all done
        if ($doneCount == count($quantities)) {
            // Watch for non completed shipment
            foreach ($shipments as $shipment) {
                if ($shipment->getState() != Ship::STATE_COMPLETED) {
                    return Ship::STATE_SHIPPED;
                }
            }
            // All clear !
            return Ship::STATE_COMPLETED;
        } elseif (0 < $partialCount) {
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
            );
        }
    }
}
