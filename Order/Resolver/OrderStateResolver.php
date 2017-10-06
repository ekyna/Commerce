<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Model\SaleItemInterface;
use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Invoice\Model\InvoiceTypes;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates as Pay;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
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

        // Order states
        if ($order->hasItems()) {
            if (Pay::isPaidState($paymentState)) {
                if (Ship::isShippedState($shipmentState)) {
                    $newState = OrderStates::STATE_COMPLETED;
                } else {
                    $newState = OrderStates::STATE_ACCEPTED;
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

        // If the new order state is not stockable and
        // - order has at least one stockable shipment
        // - order has at least one accepted payment
        // Set the new state as ACCEPTED
        if (!OrderStates::isStockableState($newState) && ($order->hasShipments() || $order->hasPayments())) {
            $accepted = false;
            foreach ($order->getShipments() as $shipment) {
                if (ShipmentStates::isStockableState($shipment->getState())) {
                    $accepted = true;
                    break;
                }
            }
            if (!$accepted) {
                foreach ($order->getPayments() as $payment) {
                    if (PaymentStates::isPaidState($payment->getState())) {
                        $accepted = true;
                        break;
                    }
                }
            }
            if ($accepted) {
                $newState = OrderStates::STATE_ACCEPTED;
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
        if (!$item->isCompound()) {
            $quantities[$item->getId()] = array(
                'ordered' => $this->getOrderedQuantity($item),
                'shipped' => 0,
                'returned' => 0,
            );
        }

        if ($item->hasChildren()) {
            foreach ($item->getChildren() as $child) {
                $this->buildOrderedQuantities($child, $quantities);
            }
        }
    }

    /**
     * Returns the ordered quantity (minus refund by credit documents).
     *
     * @param SaleItemInterface $item
     *
     * @return SaleItemInterface|float
     */
    private function getOrderedQuantity(SaleItemInterface $item)
    {
        $quantity = $item->getTotalQuantity();

        $sale = $item->getSale();
        if (!$sale instanceof OrderInterface) {
            return $quantity;
        }

        foreach ($sale->getInvoices() as $invoice) {
            if (!InvoiceTypes::isCredit($invoice)) {
                continue;
            }

            foreach ($invoice->getLines() as $line) {
                if ($item === $line->getSaleItem()) {
                    $quantity -= $line->getQuantity();
                    break;
                }
            }
        }

        if (0 < $quantity) {
            return $quantity;
        }

        return 0;
    }
}
