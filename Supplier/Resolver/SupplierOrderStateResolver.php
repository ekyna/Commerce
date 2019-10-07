<?php

namespace Ekyna\Component\Commerce\Supplier\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\AbstractStateResolver;
use Ekyna\Component\Commerce\Exception\UnexpectedTypeException;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderInterface;
use Ekyna\Component\Commerce\Supplier\Model\SupplierOrderStates;

/**
 * Class SupplierOrderStateResolver
 * @package Ekyna\Component\Commerce\Supplier\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SupplierOrderStateResolver extends AbstractStateResolver
{
    /**
     * @inheritDoc
     *
     * @param SupplierOrderInterface $subject
     */
    protected function resolveState(object $subject): string
    {
        // Current state
        $current = $subject->getState();

        // If order state is 'canceled', do nothing
        // TODO really ?
        if ($current === SupplierOrderStates::STATE_CANCELED) {
            return SupplierOrderStates::STATE_CANCELED;
        }

        // Default state is 'new'
        $resolved = SupplierOrderStates::STATE_NEW;

        // Order with 'ordered at' date is 'ordered'
        if ($subject->getOrderedAt()) {
            $resolved = SupplierOrderStates::STATE_ORDERED;

            // Order with EDA is validated
            if ($subject->getEstimatedDateOfArrival()) {
                $resolved = SupplierOrderStates::STATE_VALIDATED;
            }
        }

        // TODO Use packaging format
        $ordered = $received = 0;

        // If the order has deliveries
        if ($subject->hasDeliveries()) {
            // Gather ordered quantity and received quantities.
            foreach ($subject->getItems() as $orderItem) {
                $ordered += $orderItem->getQuantity();
                // For each deliveries
                foreach ($subject->getDeliveries() as $delivery) {
                    // For each delivery items
                    foreach ($delivery->getItems() as $deliveryItem) {
                        // If delivery item concerns current order item
                        if ($orderItem === $deliveryItem->getOrderItem()) {
                            // Increment received quantity
                            $received += $deliveryItem->getQuantity();
                            // Found: go to next delivery
                            continue 2;
                        }
                    }
                }
            }
        }

        if (0 < $received) {
            $resolved = SupplierOrderStates::STATE_PARTIAL;

            if ($ordered == $received) {
                $resolved = SupplierOrderStates::STATE_COMPLETED;

                if (null === $subject->getPaymentDate()) {
                    $resolved = SupplierOrderStates::STATE_RECEIVED;
                } elseif (null !== $subject->getCarrier() && null === $subject->getForwarderDate()) {
                    $resolved = SupplierOrderStates::STATE_RECEIVED;
                }
            }
        }

        return $resolved;
    }

    /**
     * @inheritDoc
     */
    protected function supports(object $subject): void
    {
        if (!$subject instanceof SupplierOrderInterface) {
            throw new UnexpectedTypeException($subject, SupplierOrderInterface::class);
        }
    }
}
