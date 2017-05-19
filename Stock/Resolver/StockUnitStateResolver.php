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

        if (0 < $stockUnit->getOrderedQuantity() && null !== $stockUnit->getSupplierOrderItem()) {
            // Assigned to supplier order and pending for delivery
            $resolvedState = StockUnitStates::STATE_PENDING;

            // If the whole ordered quantity (to suppliers) has been entirely shipped (to customers)
            if ($stockUnit->getOrderedQuantity() == $stockUnit->getShippedQuantity()) {
                $resolvedState = StockUnitStates::STATE_CLOSED;
            }

            // If quantity has been received (by supplier)
            elseif (0 < $stockUnit->getReceivedQuantity()) {
                // If received (from supplier) quantity has been entirely shipped (to customers)
                if ($stockUnit->getReceivedQuantity() == $stockUnit->getShippedQuantity()) {
                    // Waiting for another delivery (from suppliers)
                    $resolvedState = StockUnitStates::STATE_PENDING;
                } else {
                    // Ready for shipment (to customers)
                    $resolvedState = StockUnitStates::STATE_READY;
                }
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
