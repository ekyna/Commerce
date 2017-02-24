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
        $resolvedState = StockUnitStates::STATE_NEW;
        $currentState = $stockUnit->getState();

        if (null !== $stockUnit->getSupplierOrderItem()) {
            $resolvedState = StockUnitStates::STATE_PENDING;
        }

        // If quantity has been delivered (by supplier)
        if (0 < $stockUnit->getDeliveredQuantity()) {
            $resolvedState = StockUnitStates::STATE_OPENED;

            // If quantity has been entirely shipped (to customers)
            // TODO use bccomp() with packaging precision ?
            if ($stockUnit->getShippedQuantity() == $stockUnit->getDeliveredQuantity()) {
                $resolvedState = StockUnitStates::STATE_CLOSED;
            }
        }

        if ($currentState != $resolvedState) {
            $stockUnit->setState($resolvedState);

            if ($resolvedState === StockUnitStates::STATE_CLOSED) {
                $stockUnit->setClosedAt(new \DateTime());
            }

            return true;
        }

        return false;
    }
}
