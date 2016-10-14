<?php

namespace Ekyna\Component\Commerce\Supplier\Resolver;

use Ekyna\Component\Commerce\Common\Model\StateSubjectInterface;
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
    public function resolve(StateSubjectInterface $subject)
    {
        if (!$subject instanceof SupplierOrderInterface) {
            throw new InvalidArgumentException("Expected instance of SupplierOrderInterface.");
        }

        // Current state
        $currentState = $subject->getState();

        // If order state is 'cancelled', do nothing
        if ($currentState === SupplierOrderStates::STATE_CANCELLED) {
            return false;
        }

        // Default state is 'new'
        $resolvedState = SupplierOrderStates::STATE_NEW;

        if (null !== $subject->getOrderedAt()) {
            $resolvedState = SupplierOrderStates::STATE_ORDERED;
        } elseif ($subject->hasDeliveries()) {
            // Assume the order state is 'completed' for now
            $resolvedState = SupplierOrderStates::STATE_COMPLETED;

            // Compare ordered quantity with delivered quantities for each order items.
            foreach ($subject->getItems() as $orderItem) {
                $orderedQuantity = $orderItem->getQuantity();
                $deliveredQuantity = 0;

                // For each deliveries
                foreach ($subject->getDeliveries() as $delivery) {
                    // For each delivery items
                    foreach ($delivery->getItems() as $deliveryItem) {
                        // If delivery item concerns current order item
                        if ($orderItem === $deliveryItem->getOrderItem()) {
                            // Increment delivered quantity
                            $deliveredQuantity += $deliveryItem->getQuantity();
                            // Found: go to next delivery
                            continue 2;
                        }
                    }
                }

                // Delivered quantity is lower than ordered quantity
                if ($deliveredQuantity < $orderedQuantity) {
                    // Order state is 'partial'
                    $resolvedState = SupplierOrderStates::STATE_PARTIAL;
                    break;
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
