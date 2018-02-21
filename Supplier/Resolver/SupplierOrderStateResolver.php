<?php

namespace Ekyna\Component\Commerce\Supplier\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;

/**
 * Class SupplierOrderStateResolver
 * @package Ekyna\Component\Commerce\Supplier\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderStateResolver implements StateResolverInterface
{
    /**
     * @inheritDoc
     */
    public function resolve($subject)
    {
        if (!$subject instanceof SupplierOrderInterface) {
            throw new InvalidArgumentException("Expected instance of SupplierOrderInterface.");
        }

        // Current state
        $currentState = $subject->getState();

        // If order state is 'canceled', do nothing
        // TODO really ?
        if ($currentState === SupplierOrderStates::STATE_CANCELED) {
            return false;
        }

        // Default state is 'new'
        $resolvedState = SupplierOrderStates::STATE_NEW;

        // If ordered at is set
        if (null !== $subject->getOrderedAt()) {
            $resolvedState = SupplierOrderStates::STATE_ORDERED;
        }

        // TODO Use packaging format
        $orderedQuantity = $receivedQuantity = 0;

        // If the order has deliveries
        if ($subject->hasDeliveries()) {
            // Gather ordered quantity and received quantities.
            foreach ($subject->getItems() as $orderItem) {
                $orderedQuantity += $orderItem->getQuantity();
                // For each deliveries
                foreach ($subject->getDeliveries() as $delivery) {
                    // For each delivery items
                    foreach ($delivery->getItems() as $deliveryItem) {
                        // If delivery item concerns current order item
                        if ($orderItem === $deliveryItem->getOrderItem()) {
                            // Increment received quantity
                            $receivedQuantity += $deliveryItem->getQuantity();
                            // Found: go to next delivery
                            continue 2;
                        }
                    }
                }
            }
        }

        if (0 < $receivedQuantity ) {
            $resolvedState = SupplierOrderStates::STATE_PARTIAL;

            if ($orderedQuantity == $receivedQuantity) {
                $resolvedState = SupplierOrderStates::STATE_COMPLETED;

                if (null === $subject->getPaymentDate()) {
                    $resolvedState = SupplierOrderStates::STATE_RECEIVED;
                } elseif (null !== $subject->getCarrier() && null === $subject->getForwarderDate()) {
                    $resolvedState = SupplierOrderStates::STATE_RECEIVED;
                }
            }
        }

        // If resolved state and current state don't match
        if ($resolvedState != $currentState) {
            // Update the state
            $subject->setState($resolvedState);

            return true;
        }

        return false;
    }
}
