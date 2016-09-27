<?php

namespace Ekyna\Component\Commerce\Order\Resolver;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
use Ekyna\Component\Commerce\Common\Resolver\AbstractSaleStateResolver;
use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
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
            if ($paymentState === Pay::STATE_CAPTURED && $shipmentState === Ship::STATE_SHIPPED) {
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
            return Ship::STATE_SHIPPED;
        }

        if (!$order->hasItems()) {
            return Ship::STATE_PENDING;
        }

        // TODO Shipments states

        return Ship::STATE_PENDING;
    }
}
