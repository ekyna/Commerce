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
            $currentState = StockUnitStates::STATE_PENDING;
        }

        // If quantity has been delivered (by supplier)
        if (0 < $stockUnit->getDeliveredQuantity()) {
            $resolvedState = StockUnitStates::STATE_OPENED;

            // If quantity has been entirely shipped (to customers)
            if ($stockUnit->getShippedQuantity() == $stockUnit->getDeliveredQuantity()) { // TODO use bccomp() with packaging precision ?
                $resolvedState = StockUnitStates::STATE_CLOSED;
            }
        }

        if ($currentState != $resolvedState) {
            $stockUnit->setState($resolvedState);

            return true;
        }

        return false;
    }
}