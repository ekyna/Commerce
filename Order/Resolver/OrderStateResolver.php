<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Order\Model\OrderInterface;
use Ekyna\Component\Commerce\Order\Model\OrderStates;
use Ekyna\Component\Commerce\Payment\Model\PaymentStates;
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
    public function resolve($order)
    {
        if (!$order instanceof OrderInterface) {
            throw new InvalidArgumentException("Expected instance of OrderInterface.");
        }

        $changed = parent::resolve($order);

        $paymentState = $order->getPaymentState();
        $shipmentState = $order->getShipmentState();

        $state = OrderStates::STATE_NEW;

        // Order states
        if ($order->hasItems()) {
            if (PaymentStates::isPaidState($paymentState)) {
                if (ShipmentStates::isShippedState($shipmentState)) {
                    $state = OrderStates::STATE_COMPLETED;
                } else {
                    $state = OrderStates::STATE_ACCEPTED;
                }
            } elseif ($paymentState == PaymentStates::STATE_PENDING) {
                $state = OrderStates::STATE_PENDING;
            } elseif ($paymentState == PaymentStates::STATE_FAILED) {
                $state = OrderStates::STATE_REFUSED;
            } elseif ($paymentState == PaymentStates::STATE_REFUNDED) {
                $state = OrderStates::STATE_REFUNDED;
            } elseif ($paymentState == PaymentStates::STATE_CANCELLED) {
                $state = OrderStates::STATE_CANCELLED;
            } else {
                $state = OrderStates::STATE_NEW;
            }
        }

        // If the new order state is not stockable and
        // - order has at least one stockable shipment
        // - order has at least one accepted payment
        // Set the new state as ACCEPTED
        if (!OrderStates::isStockableState($state) && ($order->hasShipments() || $order->hasPayments())) {
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
                $state = OrderStates::STATE_ACCEPTED;
            }
        }

        if ($state !== $order->getState()) {
            $order->setState($state);
            $changed = true;
        }

        return $changed;
    }
}
