<?php

namespace Ekyna\Component\Commerce\Stock\Model;

/**
 * Class StockStates
 * @package Ekyna\Component\Commerce\Stock\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
final class StockStates
{
    const STATE_IN_STOCK     = 'in_stock';
    const STATE_PRE_ORDER    = 'pre_order';
    const STATE_OUT_OF_STOCK = 'out_of_stock';


    /**
     * Returns all the states.
     *
     * @return array
     */
    static public function getStates()
    {
        return [
            static::STATE_IN_STOCK,
            static::STATE_PRE_ORDER,
            static::STATE_OUT_OF_STOCK,
        ];
    }

    /**
     * Returns whether the given state is valid or not.
     *
     * @param string $state
     *
     * @return bool
     */
    static public function isValidState($state)
    {
        return in_array($state, static::getStates(), true);
    }

    /**
     * Returns whether the state A is better than the state B.
     *
     * @param string $stateA
     * @param string $stateB
     *
     * @return bool
     */
    static public function isBetterState($stateA, $stateB)
    {
        // TODO assert valid states ?

        if ($stateA === static::STATE_IN_STOCK) {
            return in_array($stateB, [static::STATE_PRE_ORDER, static::STATE_OUT_OF_STOCK]);
        } elseif ($stateA === static::STATE_PRE_ORDER) {
            return $stateB === static::STATE_OUT_OF_STOCK;
        }

        return false;
    }
}
