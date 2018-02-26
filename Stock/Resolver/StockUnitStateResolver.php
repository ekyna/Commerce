<?php

namespace Ekyna\Component\Commerce\Stock\Resolver;

use Ekyna\Component\Commerce\Stock\Model\StockUnitInterface;
use Ekyna\Component\Commerce\Stock\Model\StockUnitStates;

/**
 * Class StockUnitStateResolver
 * @package Ekyna\Component\Commerce\Stock\Resolver
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StockUnitStateResolver implements StockUnitStateResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(StockUnitInterface $stockUnit)
    {
        // TODO use bccomp() with packaging precision to compare quantities

        // Just created
        $resolvedState = StockUnitStates::STATE_NEW;
        $currentState = $stockUnit->getState();

        $adjusted = $stockUnit->getAdjustedQuantity();

        if (null !== $stockUnit->getSupplierOrderItem()) {
            $max = $stockUnit->getOrderedQuantity() + $adjusted;

            // Assigned to supplier order and pending for delivery
            $resolvedState = StockUnitStates::STATE_PENDING;

            // If the ordered quantity (to suppliers) + adjusted quantity (from administrators)
            // has been entirely shipped (to customers)
            if ($max == $stockUnit->getShippedQuantity()) {
                $resolvedState = StockUnitStates::STATE_CLOSED;
            }

            // If quantity has been received (by supplier)
            elseif (0 < $max = $stockUnit->getReceivedQuantity() + $adjusted) {
                // If received quantity (from supplier) + adjusted quantity (from administrators)
                // has been entirely shipped (to customers)
                if ($max == $stockUnit->getShippedQuantity()) {
                    // Waiting for another delivery (from suppliers)
                    $resolvedState = StockUnitStates::STATE_PENDING;
                } else {
                    // Ready for shipment (to customers)
                    $resolvedState = StockUnitStates::STATE_READY;
                }
            }
        } elseif (0 < $adjusted) {
            // If the whole adjusted quantity (from administrators) has been entirely shipped (to customers)
            if ($adjusted == $stockUnit->getShippedQuantity()) {
                // Only if no remaining sold quantity
                if ($adjusted == $stockUnit->getSoldQuantity()) {
                    $resolvedState = StockUnitStates::STATE_CLOSED;
                }
            } else {
                // Ready for shipment (to customers)
                $resolvedState = StockUnitStates::STATE_READY;
            }
        }

        if ($currentState != $resolvedState) {
            $stockUnit->setState($resolvedState);

            if ($resolvedState === StockUnitStates::STATE_CLOSED) {
                if (null === $stockUnit->getClosedAt()) {
                    $stockUnit->setClosedAt(new \DateTime());
                }
            } elseif (null != $stockUnit->getClosedAt()) {
                $stockUnit->setClosedAt(null);
            }

            return true;
        }

        return false;
    }
}
