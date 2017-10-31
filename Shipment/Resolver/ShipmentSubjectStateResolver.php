<?php

namespace Ekyna\Component\Commerce\Shipment\Resolver;

use Ekyna\Component\Commerce\Common\Resolver\StateResolverInterface;
use Ekyna\Component\Commerce\Exception\InvalidArgumentException;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentStates;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentSubjectInterface;

/**
 * Class ShipmentStateResolver
 * @package Ekyna\Component\Commerce\Shipment\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShipmentSubjectStateResolver implements StateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve($subject)
    {
        if (!$subject instanceof ShipmentSubjectInterface) {
            throw new InvalidArgumentException("Expected instance of " . ShipmentSubjectInterface::class);
        }

        if (!$subject->requiresShipment()) {
            return $this->setState($subject, ShipmentStates::STATE_NONE);
        }

        // Get subject item's ordered quantities
        $quantities = array_map(function ($ordered) {
            return [
                'ordered'  => $ordered,
                'shipped'  => 0,
                'returned' => 0,
            ];
        }, $subject->getSoldQuantities());

        // Build shipped and returned quantities.
        $shipments = $subject->getShipments();
        foreach ($shipments as $shipment) {
            // Ignore if shipment is not shipped or completed
            if (!ShipmentStates::isStockableState($shipment->getState())) {
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

        $itemsCount = count($quantities);

        // If all fully returned
        if ($returnedCount == $itemsCount) {
            return $this->setState($subject, ShipmentStates::STATE_RETURNED);
        }
        // Else if all fully shipped
        elseif ($shippedCount == $itemsCount) {
            // Watch for non completed shipment
            foreach ($shipments as $shipment) {
                if ($shipment->getState() != ShipmentStates::STATE_COMPLETED) {
                    return $this->setState($subject, ShipmentStates::STATE_SHIPPED);
                }
            }

            // All Clear
            return $this->setState($subject, ShipmentStates::STATE_COMPLETED);
        }
        // Else if some partially shipped
        elseif (0 < $partialCount) {
            return $this->setState($subject, ShipmentStates::STATE_PARTIAL);
        }

        return $this->setState($subject, ShipmentStates::STATE_PENDING);
    }

    /**
     * Sets the shipment state.
     *
     * @param ShipmentSubjectInterface $subject
     * @param string                   $state
     *
     * @return bool Whether the shipment state has been updated.
     */
    protected function setState(ShipmentSubjectInterface $subject, $state)
    {
        if ($state !== $subject->getShipmentState()) {
            $subject->setShipmentState($state);

            return true;
        }

        return false;
    }
}
